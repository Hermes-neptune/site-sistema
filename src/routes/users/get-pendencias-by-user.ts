import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function getPendenciasByUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/users/:userId/pendencias",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Obtém pendências de um usuário específico com validação JWT",
        tags: ["pendencias"],
        params: z.object({
          userId: z.string(),
        }),
        response: {
          200: z.array(
            z.object({
              id: z.number(),
              descricao: z.string(),
              status: z.string(),
              user_id: z.string(),
            })
          ),
          403: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { userId } = request.params;
      const tokenUserId = request.user.id;

      if (tokenUserId !== userId) {
        return reply.status(403).send({
          error: "Não autorizado a visualizar pendências de outro usuário",
        });
      }

      const pendencias = await prisma.pendencias.findMany({
        where: { user_id: userId },
        orderBy: {
          id: "desc",
        },
      });

      return reply.send(pendencias);
    }
  );
}
