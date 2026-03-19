/**
 * Interfaz de base de datos SQLite + OPFS (Compatible con Dexie para minimizar cambios)
 */
// Usamos var para asegurar que sea una variable global accesible por todos los scripts
var db;

(function() {
    console.log("Iniciando db.js...");
    
    // Usamos el archivo .php para el worker para inyectar cabeceras de seguridad
    const dbWorker = new Worker('db-worker.php');
    const pendingRequests = new Map();
    let msgCounter = 0;

    dbWorker.onerror = (err) => {
        console.error("Error crítico en el Worker de SQLite:", err);
    };

    dbWorker.onmessage = (e) => {
        const { id, status, result, error } = e.data;
        if (pendingRequests.has(id)) {
            const { resolve, reject } = pendingRequests.get(id);
            pendingRequests.delete(id);
            if (status === 'ok') resolve(result);
            else reject(new Error(error));
        }
    };

    const sendRequest = (action, table, data) => {
        return new Promise((resolve, reject) => {
            const id = msgCounter++;
            pendingRequests.set(id, { resolve, reject });
            dbWorker.postMessage({ id, action, table, data });
        });
    };

    class Table {
        constructor(tableName) {
            this.tableName = tableName;
        }

        async count() {
            return await sendRequest('count', this.tableName);
        }

        async put(data) {
            return await sendRequest('put', this.tableName, data);
        }

        async bulkPut(data) {
            return await sendRequest('bulkPut', this.tableName, data);
        }

        async delete(id) {
            return await sendRequest('delete', this.tableName, id);
        }

        async clear() {
            return await sendRequest('clear', this.tableName);
        }

        async toArray() {
            return await sendRequest('toArray', this.tableName);
        }

        orderBy(prop) {
            let _prop = prop;
            return {
                filter: (fn) => {
                    return {
                        toArray: async () => {
                            let all = await this.toArray();
                            all.sort((a, b) => a[_prop] > b[_prop] ? 1 : -1);
                            return all.filter(fn);
                        }
                    };
                },
                toArray: async () => {
                    let all = await this.toArray();
                    all.sort((a, b) => a[_prop] > b[_prop] ? 1 : -1);
                    return all;
                }
            };
        }

        filter(fn) {
            return {
                toArray: async () => {
                    const all = await this.toArray();
                    return all.filter(fn);
                }
            };
        }
    }

    db = {
        alumnos: new Table('alumnos'),
        materias: new Table('materias'),
        docentes: new Table('docentes'),
        inscripciones: new Table('inscripciones'),
        matriculas: new Table('matriculas'),
        
        // Método simulado para Dexie
        version: () => ({
            stores: () => {
                console.log('Esquema de SQLite preparado.');
                return {
                    // Simular versionamiento de Dexie si fuera necesario
                };
            }
        })
    };

    window.db = db;
    console.log("Objeto db (SQLite) inyectado en window.");
})();
