import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import z from "zod";
import { prisma } from "../../lib/prisma";

export async function AdicionarCreditosAoUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().post(
    "/creditos",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Registra uma nova entrada de créditos para um usuário",
        tags: ["creditos"],
        body: z.object({
          quantidade: z.number().int().nonnegative(),
          username: z.string(),
        }),
        response: {
          201: z.object({
            message: z.string(),
            quantidadeAtualizada: z.number(),
          }),
          404: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { quantidade, username } = request.body;

      const user = await prisma.users.findUnique({
        where: { username },
      });

      if (!user) {
        return reply.status(404).send({ error: "Usuário não encontrado" });
      }

      await prisma.creditos.create({
        data: { quantidade, username },
      });

      const total = await prisma.creditos.aggregate({
        _sum: { quantidade: true },
        where: { username },
      });

      const quantidadeAtualizada = total._sum.quantidade ?? 0;

      return reply.status(201).send({
        message: "Créditos registrados com sucesso",
        quantidadeAtualizada,
      });
    }
  );
}
