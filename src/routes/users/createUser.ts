import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";
import { hashPassword } from "../../utils/hash-utils";
import { gerarcodigo } from "../../utils/generate-code";

export async function createUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().post(
    "/users",
    {
      schema: {
        summary: "Cria um novo usuário",
        tags: ["users"],
        body: z.object({
          rm: z.string().min(3),
          username: z.string().min(3),
          nome_completo: z.string().min(3),
          email: z.string().email(),
          password: z.string().min(3),
        }),
        response: {
          201: z.object({
            message: z.string(),
          }),
          409: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { rm, username, nome_completo, email, password } = request.body;

      const userWithRm = await prisma.users.findFirst({
        where: { rm },
      });

      if (userWithRm) {
        return reply.status(409).send({ error: "RM já está cadastrado" });
      }

      const userWithSameUsername = await prisma.users.findUnique({
        where: { username },
      });

      if (userWithSameUsername) {
        return reply.status(409).send({ error: "Username já está em uso" });
      }

      const userWithSameEmail = await prisma.users.findUnique({
        where: { email },
      });
      if (userWithSameEmail) {
        return reply.status(409).send({ error: "Email já está cadastrado" });
      }

      const hashedPassword = await hashPassword(password);

      const code = await gerarcodigo();

      const user = await prisma.users.create({
        data: {
          rm,
          username,
          nome_completo,
          email,
          password: hashedPassword,
          codigo_unico: code,
        },
      });

      return reply.status(201).send({
        message: "Usuário criado com sucesso",
      });
    }
  );
}
