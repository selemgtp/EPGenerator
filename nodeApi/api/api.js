const path = require("path")

const api = {
    definition: {
      openapi: "3.0.0",
      info: {
        title: "AST SQL",
        version: "1.0.0",
        description:
          "Api Express que devuelve un ast (Abstract Sintax Tree) de una consulta SQL junto con información extraida del mismo.",
        license: {
          name: "MIT",
          url: "https://spdx.org/licenses/MIT.html",
        },
        contact: {
          name: "Nicolás Herrera",
          //url: "https://logrocket.com",
          email: "josnikh@hotmail.com",
        },
      },
      servers: [
        {
          url: "http://node.gtp.bz/:8000",
        },
      ],
    },
    apis: [path.join(__dirname,"./models/astRequestSchema.js"),path.join(__dirname,"./models/astResponseSchemas.js"), path.join(__dirname,"./routes/astRoute.js")],
  };

  exports.api = api
