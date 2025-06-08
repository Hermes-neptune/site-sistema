import fastifyCors from "@fastify/cors";
import fastify, { FastifyReply, FastifyRequest } from "fastify";
import {
  serializerCompiler,
  validatorCompiler,
} from "fastify-type-provider-zod";
import { createUser } from "./routes/users/createUser";
import fastifyJwt from "@fastify/jwt";
import { pegarUserPeloId } from "./routes/users/get-user-by-id";
import { login } from "./routes/auth/login";

const app = fastify();

app.get("/", () => {
  return "Hello World";
});

app.register(fastifyCors, {
  origin: "*",
  methods: ["GET", "POST", "PUT", "DELETE"],
  credentials: true,
});

app.register(fastifyJwt, {
  secret: process.env.JWT_SECRET_KEY || "supersecretkey",
  sign: {
    expiresIn: "1d",
  },
});

app.decorate(
  "authenticate",
  async (request: FastifyRequest, reply: FastifyReply) => {
    try {
      await request.jwtVerify();
    } catch (err) {
      reply.code(401).send({
        error: "Não autorizado. Token inválido ou expirado.",
      });
    }
  }
);

app.setValidatorCompiler(validatorCompiler);
app.setSerializerCompiler(serializerCompiler);

app.register(createUser);
app.register(pegarUserPeloId);
app.register(login);

app.listen({ port: 3333 }).then(() => {
  console.log("HTTP server running!");
});
