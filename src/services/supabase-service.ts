import { SupabaseClient } from "@supabase/supabase-js";
import { FastifyInstance } from "fastify";

type UploadFilePayload = {
  file: Buffer;
  contentType: string;
  fileName: string;
};

export class SupabaseService {
  private readonly bucketName: string;

  constructor(
    private readonly supabase: SupabaseClient,
    private readonly httpErrors: FastifyInstance["httpErrors"]
  ) {
    const bucketFromEnv = process.env.SUPABASE_BUCKET;

    if (!bucketFromEnv) {
      throw new Error("A variável de ambiente SUPABASE_BUCKET é obrigatória.");
    }

    this.bucketName = bucketFromEnv;
  }

  async upload({ file, contentType, fileName }: UploadFilePayload) {
    const { data, error } = await this.supabase.storage
      .from(this.bucketName)
      .upload(fileName, file, {
        contentType,
        upsert: false,
      });

    if (error) {
      throw this.httpErrors.internalServerError(
        `Erro ao fazer upload: ${error.message}`
      );
    }

    const { data: urlData } = this.supabase.storage
      .from(this.bucketName)
      .getPublicUrl(data.path);

    return {
      url: urlData.publicUrl,
      path: data.path,
    };
  }

  async delete(fileName: string) {
    const { data, error } = await this.supabase.storage
      .from(this.bucketName)
      .remove([fileName]);

    if (error) {
      throw this.httpErrors.internalServerError(
        `Erro ao deletar arquivo: ${error.message}`
      );
    }

    if (data.length === 0) {
      throw this.httpErrors.notFound("Arquivo não encontrado no bucket.");
    }

    return { message: `Arquivo "${fileName}" deletado com sucesso.` };
  }
}
