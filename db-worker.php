<?php
// Cabeceras para el worker de SQLite
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Resource-Policy: same-origin");
header("Content-Type: application/javascript");
?>
/**
 * Worker para manejar SQLite con OPFS
 * MODO PHP WRAPPER para saltar restricciones de Apache
 */
console.log("Worker iniciado (vía PHP), cargando SQLite WASM...");

try {
    importScripts('sqlite3.js');
} catch (e) {
    console.error("Error al importar sqlite3.js:", e);
}

let db_handle;

const getPK = (table) => {
    const pks = {
        alumnos: 'idAlumno',
        materias: 'idMateria',
        docentes: 'idDocente',
        inscripciones: 'idInscripcion',
        matriculas: 'idMatricula'
    };
    return pks[table] || 'id';
};

const initDB = async () => {
    console.log("Iniciando initDB (Corrección Final)...");
    try {
        if (typeof sqlite3InitModule === 'undefined') {
            throw new Error("sqlite3InitModule no está definido.");
        }
        
        const sqlite3 = await sqlite3InitModule();
        
        console.log("Aislamiento detectado:", self.crossOriginIsolated);
        
        let opfsEnabled = false;
        
        // El modo preferido: OO1 OpfsDb
        if ('opfs' in sqlite3.oo1) {
            try {
                db_handle = new sqlite3.oo1.OpfsDb('/db_academica.sqlite3', 'c');
                opfsEnabled = true;
                console.log('SQLite + OPFS (OO1) inicializado.');
            } catch (e) {
                console.warn("Fallo OO1, intentando CAPI:", e);
            }
        }

        // Si falla, probamos con el VFS nativo por nombre
        if (!opfsEnabled) {
            try {
                // Buscamos si el VFS 'opfs' está registrado
                if (sqlite3.capi.sqlite3_vfs_find('opfs')) {
                    db_handle = new sqlite3.oo1.DB('/db_academica.sqlite3', 'c', 'opfs');
                    opfsEnabled = true;
                    console.log('SQLite + OPFS (VFS Nativo) inicializado.');
                }
            } catch (e) {
                console.error("Fallo total en detección OPFS:", e);
            }
        }
        
        if (!opfsEnabled) {
            throw new Error("OPFS TOTALMENTE INDISPONIBLE. Aislamiento=" + self.crossOriginIsolated);
        }

        const tables = ['alumnos', 'materias', 'docentes', 'inscripciones', 'matriculas'];
        tables.forEach(table => {
            db_handle.exec(`CREATE TABLE IF NOT EXISTS ${table} (id TEXT PRIMARY KEY, data TEXT);`);
        });
        
        return true;
    } catch (e) {
        console.error('FALLO EN EL ESPACIO DE TRABAJO:', e);
        return false;
    }
};

const initializedPromise = initDB();

self.onmessage = async (e) => {
    const ok = await initializedPromise;
    if (!ok || !db_handle) {
        self.postMessage({ id: e.data.id, status: 'error', error: 'Database strictly requires OPFS.' });
        return;
    }
    
    const { action, table, data, id } = e.data;

    try {
        let result;
        switch (action) {
            case 'count':
                result = db_handle.selectValue(`SELECT COUNT(*) FROM ${table}`);
                break;
            case 'put':
                const pk = getPK(table);
                db_handle.exec({
                    sql: `INSERT OR REPLACE INTO ${table} (id, data) VALUES (?, ?)`,
                    bind: [data[pk], JSON.stringify(data)]
                });
                result = true;
                break;
            case 'bulkPut':
                const bPk = getPK(table);
                db_handle.exec("BEGIN TRANSACTION");
                for (const item of data) {
                    db_handle.exec({
                        sql: `INSERT OR REPLACE INTO ${table} (id, data) VALUES (?, ?)`,
                        bind: [item[bPk], JSON.stringify(item)]
                    });
                }
                db_handle.exec("COMMIT");
                result = true;
                break;
            case 'delete':
                db_handle.exec({
                    sql: `DELETE FROM ${table} WHERE id = ?`,
                    bind: [data]
                });
                result = true;
                break;
            case 'clear':
                db_handle.exec(`DELETE FROM ${table}`);
                result = true;
                break;
            case 'toArray':
                result = [];
                db_handle.exec({
                    sql: `SELECT data FROM ${table}`,
                    callback: (row) => {
                        result.push(JSON.parse(row[0]));
                    }
                });
                break;
            default:
                throw new Error('Action not supported');
        }
        self.postMessage({ id, status: 'ok', result });
    } catch (error) {
        self.postMessage({ id, status: 'error', error: error.message });
    }
};
