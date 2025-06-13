// ARQUIVO: get-atual-user.ts

import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import z from "zod";
import { prisma } from "../../lib/prisma";

export async function getAtualUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/users/me",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Obtém dados do usuário autenticado",
        tags: ["user"],
        response: {
          200: z.object({
            success: z.boolean(),
            user: z.object({
              id: z.string().uuid(),
              username: z.string(),
              rm: z.string(),
              email: z.string().email(),
              photo: z.string().nullable(),
            }),
          }),
          404: z.object({
            message: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const userId = request.user.id;

      const user = await prisma.users.findUnique({
        where: {
          id: userId,
        },
      });

      if (!user) {
        return reply.status(404).send({ message: "Usuário não encontrado" });
      }

      return reply.send({
        success: true,
        user: {
          id: user.id,
          username: user.username,
          rm: user.rm,
          email: user.email,
          photo: "https://picsum.photos/600/400",
        },
      });
    }
  );
}
