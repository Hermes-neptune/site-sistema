import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function getMensagens(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/mensagens",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Obtém todas as mensagens",
        tags: ["mensagens"],
        response: {
          200: z.array(
            z.object({
              id: z.number(),
              user_id: z.string(),
              mensagem: z.string(),
              data_criacao: z.date(),
              user: z.object({
                id: z.string(),
                username: z.string(),
                nome_completo: z.string(),
              }),
            })
          ),
        },
      },
    },
    async (request, reply) => {
      const mensagens = await prisma.mensagens.findMany({
        include: {
          user: {
            select: {
              id: true,
              username: true,
              nome_completo: true,
            },
          },
        },
        orderBy: {
          data_criacao: "desc",
        },
      });

      return reply.send(mensagens);
    }
  );
}