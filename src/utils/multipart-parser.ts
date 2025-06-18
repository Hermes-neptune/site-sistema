import { FastifyRequest } from "fastify";
import path from "node:path";
import { HttpError } from "@fastify/sensible";

type ProcessedFile = {
  buffer: Buffer;
  mimetype: string;
  extname: string;
  filename: string;
};

type ParsedForm = {
  fields: Record<string, any>;
  file: ProcessedFile | null;
};

export async function parseMultipartForm(
  request: FastifyRequest,
  allowedFileTypes: string[],
  fileFieldname: string
): Promise<ParsedForm> {
  // Verifique se a requisição é multipart
  if (!request.isMultipart()) {
    // request.server.httpErrors está disponível por causa do plugin fastify-sensible
    throw request.server.httpErrors.badRequest(
      "A requisição deve ser do tipo multipart/form-data."
    );
  }

  const parts = request.parts();
  const result: ParsedForm = {
    fields: {},
    file: null,
  };

  try {
    for await (const part of parts) {
      if (part.type === "file" && part.fieldname === fileFieldname) {
        // Ignora arquivos vazios
        if (part.filename.length === 0) {
          continue;
        }

        const extname = path.extname(part.filename).toLowerCase();
        if (!allowedFileTypes.includes(extname)) {
          throw request.server.httpErrors.badRequest(
            `Tipo de arquivo inválido. Permitidos: ${allowedFileTypes.join(
              ", "
            )}`
          );
        }
        result.file = {
          buffer: await part.toBuffer(),
          mimetype: part.mimetype,
          extname,
          filename: part.filename,
        };
      } else if (part.type === "field") {
        result.fields[part.fieldname] = part.value;
      }
    }
  } catch (error) {
    // Se o erro já for um HttpError, apenas relance-o
    if (error instanceof HttpError) {
      throw error;
    }
    // Log e lança um erro genérico do servidor
    request.log.error(error, "Erro ao processar formulário multipart.");
    throw request.server.httpErrors.internalServerError(
      "Erro ao processar os dados do formulário."
    );
  }

  return result;
}
