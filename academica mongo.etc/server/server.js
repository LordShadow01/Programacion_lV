const webpush = require('web-push'),
    VAPID_PUBLIC_KEY = 'BCJoKOi4Ypa9fc0CdJ6EdE9vR2l9SOqO30_0ZWdiZj1W2k2CDbZMqcr_QY0kRIVFkaSEBvoPNgyDRGolAg-KhkM',
    VAPID_PRIVATE_KEY = 'oU-LZR-5JdQb4bx3otdudM6Ph1WHnM1ecSGLYJ0zXnY';

var express = require('express'),
    app = express(),
    http = require('http').createServer(app),
    io = require('socket.io')(http),
    {MongoClient, ObjectId} = require('mongodb'),
    url = 'mongodb://localhost:27017',
    client = new MongoClient(url),
    dbname = 'chats_ugb',
    port = 3000;

const crypto = require('crypto');
global.crypto = crypto.webcrypto;

webpush.setVapidDetails(
    'mailto:luishernandez@ugb.edu.sv',
    VAPID_PUBLIC_KEY,
    VAPID_PRIVATE_KEY
);

app.use(express.json()); //para que pueda leer json
app.use(express.static(__dirname)); //para servir archivos estaticos js, css, img...
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/index.html');
});
app.post('/api/alumnos', async (req, res) => {
    let data = req.body,
    db = await conectarMongo(),
    collection = db.collection('alumnos'),
    result = await collection.insertOne(data);
    res.send({msg:result});
});
app.put('/api/alumnos', async (req, res) => {
    let data = req.body,
    db = await conectarMongo(),
    collection = db.collection('alumnos'),
    result = await collection.updateOne({_id:new ObjectId(data.idalumno)},{$set:data});
    res.send({msg:result});
});
app.delete('/api/alumnos/:idalumno', async (req, res) => {
    let idalumno = req.params.idalumno,
    db = await conectarMongo(),
    collection = db.collection('alumnos'),
    result = await collection.deleteOne({_id:new ObjectId(idalumno)});
    res.send({msg:result});
});
app.get('/api/alumnos', async (req, res) => {
    let buscar = req.query.buscar,
        db = await conectarMongo(),
        collection = db.collection('alumnos'),
        result = await collection.find({
            $or:[   
                {codigo:{$regex:buscar,$options:'i'}},
                {nombre:{$regex:buscar,$options:'i'}}
            ]
        }).toArray();
    res.send(result);
});

app.post('/api/suscripcion', async (req, res) => {
    let subscription = req.body,
        db = await conectarMongo(),
        collection = db.collection('suscripciones');
    await collection.updateOne(
        { endpoint: subscription.endpoint },
        { $set: subscription },
        { upsert: true }
    );
    res.status(201).json({ ok: true });
});

async function conectarMongo(){
    await client.connect();
    return client.db(dbname);
}

io.on('connect', (socket) => {
    console.log('Un usuario se ha conectado');

    socket.on('mensajeRecibido', async (data) => {
        let db = await conectarMongo(),
            collection = db.collection('chats'),
            result = collection.insertOne({user:data.titulo, mensaje:data.mensaje, fecha:new Date()});
        io.emit('mensajeEnviar', data);

        // Enviar notificaciones push a todos los usuarios suscritos
        try {
            let collectionSuscripciones = db.collection('suscripciones'),
                suscripciones = await collectionSuscripciones.find().toArray(),
                payload = JSON.stringify({
                    title: data.titulo,
                    body: data.mensaje
                });
            suscripciones.forEach(sub => {
                webpush.sendNotification(sub, payload)
                    .catch(err => {
                        console.error('Error al enviar notificacion push:', err);
                        // Si la suscripción ya no es válida, la removemos
                        if (err.statusCode === 410 || err.statusCode === 404) {
                            collectionSuscripciones.deleteOne({ endpoint: sub.endpoint })
                                .then(() => console.log('Suscripción obsoleta eliminada:', sub.endpoint))
                                .catch(dbErr => console.error('Error al borrar suscripción obsoleta:', dbErr));
                        }
                    });
            });
        } catch (error) {
            console.error('Error al procesar notificaciones push:', error);
        }
    })
});

http.listen(port, () => {
    console.log('Escuchando en el puerto ', port);
});
