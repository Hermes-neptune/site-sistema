import { FastifyInstance } from "fastify";
import { ZodTypeProvider } from "fastify-type-provider-zod";
import { z } from "zod";
import { prisma } from "../../lib/prisma";
import { hashPassword } from "../../utils/hash-utils";
import { gerarcodigo } from "../../utils/generate-code";
import { parseMultipartForm } from "../../utils/multipart-parser";
import { sanitizeUsername } from "../../utils/sanitize";

export async function createUser(app: FastifyInstance) {
  app.withTypeProvider<ZodTypeProvider>().post(
    "/users",
    {
      schema: {
        summary: "Cria um novo usuário",
        tags: ["users"],
        consumes: ["multipart/form-data"],
        swaggerSchema: {
          body: {
            type: "object",
            properties: {
              username: {
                type: "string",
                minLength: 3,
                description: "Nome de usuário único",
                example: "joao123",
              },
              nome_completo: {
                type: "string",
                minLength: 3,
                description: "Nome completo do usuário",
                example: "João da Silva",
              },
              email: {
                type: "string",
                format: "email",
                description: "Email do usuário",
                example: "joao@exemplo.com",
              },
              password: {
                type: "string",
                minLength: 3,
                description: "Senha do usuário",
                example: "minhasenha123",
              },
              photo: {
                type: "string",
                format: "binary",
                description: "Foto de perfil do usuário (JPG, JPEG, PNG, WEBP)",
              },
            },
            required: ["username", "nome_completo", "email", "password"],
            additionalProperties: false,
          },
        },
        response: {
          201: z.object({
            message: z.string(),
            user: z.object({
              id: z.string(),
              rm: z.number(),
              username: z.string(),
              nome_completo: z.string(),
              email: z.string(),
              photo: z.string().nullable(),
            }),
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
          409: z.object({
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
        const { fields, file } = await parseMultipartForm(
          request,
          [".jpg", ".jpeg", ".png", ".webp"],
          "photo"
        );

        const createUserSchema = z.object({
          username: z
            .string()
            .min(3, "Username deve ter pelo menos 3 caracteres"),
          nome_completo: z
            .string()
            .min(3, "Nome completo deve ter pelo menos 3 caracteres"),
          email: z.string().email("Email deve ser válido"),
          password: z.string().min(3, "Senha deve ter pelo menos 3 caracteres"),
        });

        const validation = createUserSchema.safeParse(fields);

        if (!validation.success) {
          return reply.status(400).send({
            error: "Dados inválidos",
            details: validation.error.issues.map((issue) => ({
              field: issue.path.join("."),
              message: issue.message,
            })),
          });
        }

        const { username, nome_completo, email, password } = validation.data;

        const rm = await gerarcodigo();
        const usernameSanitized = sanitizeUsername(username);

        const userWithSameUsername = await prisma.users.findUnique({
          where: { username: usernameSanitized },
        });

        if (userWithSameUsername) {
          return reply.status(409).send({ error: "Username já está em uso" });
        }

        const userWithSameEmail = await prisma.users.findUnique({
          where: { email },
        });

        if (userWithSameEmail) {
          return reply.status(409).send({ error: "Email já está cadastrado" });
        }

        const hashedPassword = await hashPassword(password);

        let photoUrl: string | null = null;

        if (file) {
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
        }

        const user = await prisma.users.create({
          data: {
            rm,
            username: usernameSanitized,
            nome_completo,
            email,
            password: hashedPassword,
            photo: photoUrl,
          },
        });

        return reply.status(201).send({
          message: "Usuário criado com sucesso",
          user: {
            id: user.id,
            rm: user.rm,
            username: user.username,
            nome_completo: user.nome_completo,
            email: user.email,
            photo: user.photo,
          },
        });
      } catch (error) {
        request.log.error(error, "Erro ao criar usuário");
        return reply.status(500).send({
          error: "Erro interno do servidor",
        });
      }
    }
  );
}
