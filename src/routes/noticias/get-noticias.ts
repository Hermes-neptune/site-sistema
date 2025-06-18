import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function getNoticias(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/noticias",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Obtém todas as notícias",
        tags: ["noticias"],
        response: {
          200: z.array(
            z.object({
              id: z.number(),
              assunto: z.string(),
              detalhes: z.string(),
              data: z.date(),
            })
          ),
        },
      },
    },
    async (request, reply) => {
      const noticias = await prisma.noticias.findMany({
        orderBy: {
          data: "desc",
        },
      });

      return reply.send(noticias);
    }
  );
}