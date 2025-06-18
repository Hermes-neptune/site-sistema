import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";
import { toZonedTime, format } from "date-fns-tz";
import { startOfWeek, endOfWeek } from "date-fns";

export async function getPresencasSemanaAtual(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().get(
    "/users/:userId/presencas/semana-atual",
    {
      onRequest: [app.authenticate],
      schema: {
        summary:
          "Obtém os dias da semana atual com status de presença (domingo a sábado)",
        tags: ["presencas"],
        params: z.object({ userId: z.string() }),
        response: {
          200: z.object({
            dias: z.array(
              z.object({
                data: z.string(),
                diaDaSemana: z.string(),
                diaDoMes: z.number(),
                presente: z.boolean(),
              })
            ),
            inicioSemana: z.string(),
            fimSemana: z.string(),
          }),
          403: z.object({ error: z.string() }),
        },
      },
    },
    async (request, reply) => {
      const { userId } = request.params;
      if (request.user.id !== userId) {
        return reply.status(403).send({ error: "Não autorizado" });
      }

      const timeZone = "America/Sao_Paulo";

      // 1. Pega data atual em UTC
      const now = new Date();

      // 2. Converte para horário de São Paulo
      const zonedNow = toZonedTime(now, timeZone);

      // 3. Calcula início e fim da semana (domingo a sábado)
      const inicioZoned = startOfWeek(zonedNow, { weekStartsOn: 0 });
      const fimZoned = endOfWeek(zonedNow, { weekStartsOn: 0 });

      // 4. Formata como ISO com offset -03:00
      const inicioIso = format(inicioZoned, "yyyy-MM-dd'T'HH:mm:ssXXX", {
        timeZone,
      });
      const fimIso = format(fimZoned, "yyyy-MM-dd'T'HH:mm:ssXXX", { timeZone });

      // 5. Converte para Date e envia pro Prisma
      const presencas = await prisma.presencas.findMany({
        where: {
          user_id: userId,
          data: { gte: new Date(inicioIso), lte: new Date(fimIso) },
        },
      });

      const diasComPresenca = new Set(
        presencas.map((p) => format(p.data, "yyyy-MM-dd", { timeZone }))
      );

      const nomesDias = [
        "Domingo",
        "Segunda",
        "Terça",
        "Quarta",
        "Quinta",
        "Sexta",
        "Sábado",
      ];
      const dias: Array<{
        data: string;
        diaDaSemana: string;
        diaDoMes: number;
        presente: boolean;
      }> = [];

      for (let i = 0; i < 7; i++) {
        const d = new Date(inicioZoned);
        d.setDate(inicioZoned.getDate() + i);
        const dataStr = format(d, "yyyy-MM-dd", { timeZone });
        dias.push({
          data: dataStr,
          diaDaSemana: nomesDias[i],
          diaDoMes: d.getDate(),
          presente: diasComPresenca.has(dataStr),
        });
      }

      return reply.send({
        dias,
        inicioSemana: format(inicioZoned, "yyyy-MM-dd", { timeZone }),
        fimSemana: format(fimZoned, "yyyy-MM-dd", { timeZone }),
      });
    }
  );
}
