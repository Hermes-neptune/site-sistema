import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import z from "zod";
import { prisma } from "../../lib/prisma";

export async function removerCreditosDoUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().post(
    "/creditos/remover",
    {
      onRequest: [app.authenticate],
      schema: {
        summary: "Remove créditos de um usuário",
        tags: ["creditos"],
        body: z.object({
          quantidade: z.number().int().positive(),
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
          400: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      const { quantidade, username } = request.body;

      const user = await prisma.users.findUnique({ where: {  username } });
      if (!user) {
        return reply.status(404).send({ error: "Usuário não encontrado" });
      }

      const total = await prisma.creditos.aggregate({
        _sum: { quantidade: true },
        where: { username },
      });

      const saldoAtual = total._sum.quantidade ?? 0;
      if (saldoAtual < quantidade) {
        return reply.status(400).send({
          error: "Saldo insuficiente para remover essa quantidade de créditos",
        });
      }

      await prisma.creditos.create({
        data: { quantidade: -quantidade, username },
      });

      const totalAtualizado = await prisma.creditos.aggregate({
        _sum: { quantidade: true },
        where: { username },
      });

      const quantidadeAtualizada = totalAtualizado._sum.quantidade ?? 0;

      return reply.status(201).send({
        message: "Créditos removidos com sucesso",
        quantidadeAtualizada,
      });
    }
  );
}
