import fastify, { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";

export async function pegarUserPeloId(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/users/:id",
    {
      preHandler: [app.authenticate],
      schema: {
        summary: "Obtém um usuário pelo ID",
        tags: ["users"],
        params: z.object({
          id: z.string().uuid(),
        }),
        response: {
          200: z.object({
            id: z.string().uuid(),
            username: z.string(),
            nome_completo: z.string(),
            email: z.string().email(),
            rm: z.number().int(),
          }),
          404: z.object({
            error: z.string(),
          }),
        },
        security: [{ bearerAuth: [] }],
      },
    },
    async (request, reply) => {
      const { id } = request.params;

      const user = await prisma.users.findUnique({
        where: { id },
        select: {
          id: true,
          username: true,
          nome_completo: true,
          email: true,
          rm: true,
        },
      });

      if (!user) {
        return reply.status(404).send({ error: "Usuário não encontrado" });
      }

      return reply.send(user);
    }
  );
}
