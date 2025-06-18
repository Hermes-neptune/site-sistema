import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";
import { parseMultipartForm } from "../../utils/multipart-parser";

export async function updateUserPhoto(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().patch(
    "/users/:id/photo",
    {
      schema: {
        summary: "Atualiza a foto de perfil do usuário",
        tags: ["users"],
        consumes: ["multipart/form-data"],
        params: z.object({
          id: z.string().uuid("ID deve ser um UUID válido"),
        }),
        swaggerSchema: {
          params: {
            type: "object",
            properties: {
              id: {
                type: "string",
                format: "uuid",
                description: "ID do usuário",
                example: "123e4567-e89b-12d3-a456-426614174000",
              },
            },
            required: ["id"],
          },
          body: {
            type: "object",
            properties: {
              photo: {
                type: "string",
                format: "binary",
                description:
                  "Nova foto de perfil do usuário (JPG, JPEG, PNG, WEBP)",
              },
            },
            required: ["photo"],
            additionalProperties: false,
          },
        },
        response: {
          200: z.object({
            message: z.string(),

          }),
          400: z.object({
            error: z.string(),
            details: z
              .array(
                z.object({
                  field: z.string(),
                  message: z.string(),
                })
              )
              .optional(),
          }),
          404: z.object({
            error: z.string(),
          }),
          500: z.object({
            error: z.string(),
          }),
        },
      },
    },
    async (request, reply) => {
      try {
        const paramsSchema = z.object({
          id: z.string().uuid("ID deve ser um UUID válido"),
        });

        const paramsValidation = paramsSchema.safeParse(request.params);

        if (!paramsValidation.success) {
          return reply.status(400).send({
            error: "Parâmetros inválidos",
            details: paramsValidation.error.issues.map((issue) => ({
              field: issue.path.join("."),
              message: issue.message,
            })),
          });
        }

        const { id } = paramsValidation.data;

        const existingUser = await prisma.users.findUnique({
          where: { id },
        });

        if (!existingUser) {
          return reply.status(404).send({
            error: "Usuário não encontrado",
          });
        }

        const { file } = await parseMultipartForm(
          request,
          [".jpg", ".jpeg", ".png", ".webp"], 
          "photo"
        );

        if (!file) {
          return reply.status(400).send({
            error: "Foto é obrigatória",
          });
        }

        let photoUrl: string | null = null;

        try {
          const fileName = `user-photos/${Date.now()}-${file.filename}`;
          const uploadResult = await request.server.supabaseService.upload({
            file: file.buffer,
            contentType: file.mimetype,
            fileName: fileName,
          });
          photoUrl = uploadResult.url;
        } catch (uploadError) {
          request.log.error(uploadError, "Erro ao fazer upload da foto");
          return reply.status(500).send({
            error: "Erro ao fazer upload da imagem",
          });
        }

        const updatedUser = await prisma.users.update({
          where: { id },
          data: {
            photo: photoUrl,
          },
        });

        return reply.status(200).send({
          message: "Foto do usuário atualizada com sucesso",
        });
      } catch (error) {
        request.log.error(error, "Erro ao atualizar foto do usuário");
        return reply.status(500).send({
          error: "Erro interno do servidor",
        });
      }
    }
  );
}
