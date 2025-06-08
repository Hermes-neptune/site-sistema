import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function pegarCreditosDoUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/creditos/user/:username",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Obtém a soma total dos créditos de um usuário",
        tags: ["creditos"],
        params: z.object({
          username: z.string(),
        }),
        response: {
          200: z.object({
            totalCreditos: z.number(),
          }),
          404: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { username } = request.params;

      const total = await prisma.creditos.aggregate({
        _sum: {
          quantidade: true,
        },
        where: {
          username
        },
      });

      if (!total._sum.quantidade) {
        return reply.status(404).send({
          error: "Créditos não encontrados para este usuário",
        });
      }

      return reply.send({ totalCreditos: total._sum.quantidade });
    }
  );
}
