import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";
import { verifyPassword } from "../../utils/hash-utils";

export async function login(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().post(
    "/auth/login",
    {
      schema: {
        summary: "Realiza o login de um usuário",
        tags: ["auth"],
        body: z.object({
          rm: z.number().int(),
          password: z.string().min(1),
        }),
        response: {
          200: z.object({
            message: z.string(),
            token: z.string(),
            user: z.object({
              id: z.string().uuid(),
              username: z.string(),
              nome_completo: z.string(),
              email: z.string().email(),
              rm: z.number().int(),
            }),
          }),
          401: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { rm, password } = request.body;

      const user = await prisma.users.findFirst({
        where: { rm },
      });

      if (!user) {
        return reply.status(401).send({ error: "Credenciais inválidas" });
      }

      const passwordValid = verifyPassword(password, user.password);

      if (!passwordValid) {
        return reply.status(401).send({ error: "Credenciais inválidas" });
      }

      const token = app.jwt.sign({
        id: user.id,
        username: user.username,
      });

      return reply.send({
        message: "Login realizado com sucesso",
        token,
        user: {
          id: user.id,
          username: user.username,
          nome_completo: user.nome_completo,
          email: user.email,
          rm: user.rm,
        },
      });
    }
  );
}
