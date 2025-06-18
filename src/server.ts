import fastifyCors from "@fastify/cors";
import fastify, { FastifyReply, FastifyRequest } from "fastify";
import {
  jsonSchemaTransform,
  serializerCompiler,
  validatorCompiler,
  ZodTypeProvider,
} from "fastify-type-provider-zod";
import { createUser } from "./routes/users/createUser";
import fastifyJwt from "@fastify/jwt";
import { pegarUserPeloId } from "./routes/users/get-user-by-id";
import { login } from "./routes/auth/login";
import { pegarCreditosDoUser } from "./routes/creditos/get-creditos-by-user";
import { AdicionarCreditosAoUser } from "./routes/creditos/add-creditos";
import { removerCreditosDoUser } from "./routes/creditos/remove-creditos";
import { getMensagens } from "./routes/mensagens/get-mensagens";
import { getNoticias } from "./routes/noticias/get-noticias";
import { getPendenciasByUser } from "./routes/users/get-pendencias-by-user";
import { getPresencasSemanaAtual } from "./routes/users/get-presencas-semana";
import { checkinPresenca } from "./routes/users/checkin-presenca";
import fastifyStatic from "@fastify/static";
import multipart from "@fastify/multipart";
import path from "path";
import { getAtualUser } from "./routes/users/get-user-data";
import { SupabaseService } from "./services/supabase-service";
import { supabaseClient } from "./lib/supabase";
import fastifySwagger from "@fastify/swagger";
import fastifySwaggerUI from "@fastify/swagger-ui";
import fastifySensible from "@fastify/sensible";
import { customJsonSchemaTransform } from "./utils/swagger-transform";
import { updateUserPhoto } from "./routes/users/update-user-photo";
const app = fastify({
  logger: true, // Habilita o logger padrão (JSON)
}).withTypeProvider<ZodTypeProvider>();

app.register(fastifyStatic, {
  root: path.join(__dirname, "..", "public"),
  prefix: "/",
});

app.register(fastifyCors, {
  origin: "*",
  methods: ["GET", "POST", "PUT", "DELETE"],
  credentials: true,
});

app.register(fastifyJwt, {
  secret: process.env.JWT_SECRET_KEY,
  sign: {
    expiresIn: "1d",
  },
});

app.register(fastifySensible);

app.decorate("authenticate", async (request, reply) => {
  try {
    await request.jwtVerify();
  } catch (err) {
    reply.code(401).send({
      error: "Não autorizado. Token inválido ou expirado.",
    });
  }
});

const supabaseService = new SupabaseService(supabaseClient, app.httpErrors);
app.decorate("supabaseService", supabaseService);

app.setValidatorCompiler(validatorCompiler);
app.setSerializerCompiler(serializerCompiler);

app.register(multipart);

app.register(fastifySwagger, {
  openapi: {
    info: {
      title: "Minha API", // Mude para o nome do seu projeto
      description: "Documentação da API do meu projeto.",
      version: "1.0.0",
    },

    servers: [{ url: "http://localhost:3000" }],
    components: {
      securitySchemes: {
        bearerAuth: {
          type: "http",
          scheme: "bearer",
          bearerFormat: "JWT",
        },
      },
    },
    security: [
      {
        bearerAuth: [],
      },
    ],
  },
  transform: customJsonSchemaTransform,
});

app.register(fastifySwaggerUI, {
  routePrefix: "/api",
});

app.register(createUser);
app.register(pegarUserPeloId);
app.register(login);
app.register(pegarCreditosDoUser);
app.register(AdicionarCreditosAoUser);
app.register(removerCreditosDoUser);
app.register(getMensagens);
app.register(getNoticias);
app.register(getPendenciasByUser);
app.register(getPresencasSemanaAtual);
app.register(checkinPresenca);
app.register(getAtualUser);
app.register(updateUserPhoto);

app.listen({ port: 3000 }).then(() => {
  console.log("HTTP server running!");
});
