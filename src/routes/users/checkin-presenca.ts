import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function checkinPresenca(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/users/checkin",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Registra a presença do dia atual usando apenas o JWT",
        tags: ["presencas"],
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
      const user_id = request.user.id;

      const now = new Date();
      const startOfDay = new Date(now);
      startOfDay.setHours(0, 0, 0, 0);
      const endOfDay = new Date(startOfDay);
      endOfDay.setDate(endOfDay.getDate() + 1);

      const existingPresenca = await prisma.presencas.findFirst({
        where: {
          user_id,
          data: {
            gte: startOfDay,
            lt: endOfDay,
          },
        },
      });

      if (existingPresenca) {
        return reply.status(409).send({
          error: "Presença já registrada para hoje",
        });
      }

      await prisma.presencas.create({
        data: {
          user_id,
          data: startOfDay,
        },
      });

      return reply.status(201).send({
        message: "Presença registrada com sucesso",
      });
    }
  );
}
