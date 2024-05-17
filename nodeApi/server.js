var express = require('express');
var app = express();
var cors = require('cors')
var bodyParser = require("body-parser");
var server = require('http').createServer(app);
var validate = require('express-jsonschema').validate;

const {ast,astSchema} = require("./src/ast")

const {api} = require('./api/api')

swaggerJsdoc = require("swagger-jsdoc"),
swaggerUi = require("swagger-ui-express");


var port =  8000;
//var port = process.env.PORT || 3000;
server.listen(port, () => {
    console.log(`Express server listening on port ${port}`);
});
app.use(cors())
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

const specs = swaggerJsdoc(api);

app.use(
    "/api-docs",
    swaggerUi.serve,
    swaggerUi.setup(specs, { explorer: true })
  );

  app.get('/', function(req, res) {
    res.send('Working!');
  });

app.route('/v1/ast')
    .post(validate({ body: astSchema }), function(req, res) {
       
        ast(req,res);

    })
    .all(function(req, res) {
        res.setHeader('Content-Type', 'application/json');
        res.status(405).send(JSON.stringify({
            "status": 405,
            "text": "METHOD_NOT_ALLOWED",
            "description": "Metodo " + req.method + " no permitido"
        }));
    })

app.use(function(req, res) {
    res.setHeader('Content-Type', 'application/json');
    res.status(404).send(JSON.stringify({
        "status": 404,
        "message": "NOT_FOUND",
        "description": "La url solicitada no se encuentra"
    }));
});

app.use(function(err, req, res, next) {
    console.log(err)
    if (err.name === 'JsonSchemaValidation') {
       
        console.log(err.message);
      
        res.setHeader('Content-Type', 'application/json');
        res.status(400).send(JSON.stringify({
            "status": 400,
            "message": "BAD_REQUEST",
            //"description": err.validations
            //"description": err.message
            "description":"Payload no válido"
        }));


    } else {
        // pass error to next error middleware handler 
        // next(err);
        console.log(err.stack)
        res.setHeader('Content-Type', 'application/json');
        res.status(500).send(JSON.stringify({
            "status": 500,
            "message": "INTERNAL_SERVER_ERROR",
            "description": err.name
        }));
    }
});


