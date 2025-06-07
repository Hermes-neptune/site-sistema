import fastifyCors from "@fastify/cors";
import fastify from "fastify";
import {
  serializerCompiler,
  validatorCompiler,
} from "fastify-type-provider-zod";
import { createUser } from "./routes/users/createUser";

const app = fastify();

app.get("/", () => {
  return "Hello World";
});

app.register(fastifyCors, {
  origin: "*",
});

app.setValidatorCompiler(validatorCompiler);
app.setSerializerCompiler(serializerCompiler);

app.register(createUser);

app.listen({ port: 3333 }).then(() => {
  console.log("HTTP server running!");
});
