// src/utils/swagger-transform.ts
import { FastifySchema } from "fastify";
import { jsonSchemaTransform } from "fastify-type-provider-zod";

interface TransformProps {
  schema: FastifySchema;
  url: string;
}

interface TransformResult {
  schema: FastifySchema;
  url: string;
}

interface SwaggerFormDataSchema {
  body?: {
    type: "object";
    properties: Record<string, any>;
    required?: string[];
    additionalProperties?: boolean;
  };
  consumes?: string[];
  [key: string]: any;
}

/**
 * Um transformador customizado para o fastify-swagger que lida com rotas multipart/form-data.
 * Para rotas form-data, permite definir manualmente o schema do Swagger através da propriedade 'swaggerSchema'.
 */
export const customJsonSchemaTransform = ({
  schema,
  url,
}: TransformProps): TransformResult => {
  // Verifica se é uma rota multipart/form-data
  if (schema.consumes?.includes("multipart/form-data")) {
    // Verifica se existe um schema customizado para o Swagger
    const customSchema = (schema as any).swaggerSchema as SwaggerFormDataSchema;

    if (customSchema) {
      console.log(`Usando schema customizado para rota form-data: ${url}`);

      // Remove o swaggerSchema e body do schema final e usa o customizado
      const { swaggerSchema, body, ...restOfSchema } = schema as any;

      const finalSchema = {
        ...restOfSchema,
        ...customSchema,
        consumes: ["multipart/form-data"],
      };

      return {
        schema: finalSchema as FastifySchema,
        url,
      };
    }

    // Fallback: se não tem schema customizado, remove o body do Zod e outros schemas de validação
    console.log(`Removendo validação de body para rota form-data: ${url}`);

    // Remove body, params, querystring, headers para evitar validação
    const { body, params, querystring, headers, ...restOfSchema } =
      schema as any;

    // Aplica transform apenas nos schemas que sobraram (geralmente só response)
    const schemasToTransform = { ...restOfSchema };

    // Remove campos vazios
    Object.keys(schemasToTransform).forEach((key) => {
      if (!schemasToTransform[key]) {
        delete schemasToTransform[key];
      }
    });

    let transformedSchema = {};

    if (Object.keys(schemasToTransform).length > 0) {
      const { schema: zodTransformedSchema } = jsonSchemaTransform({
        schema: schemasToTransform,
        url,
      });

      if (
        typeof zodTransformedSchema === "object" &&
        zodTransformedSchema !== null
      ) {
        transformedSchema = zodTransformedSchema;
      }
    }

    const finalSchema = {
      ...transformedSchema,
      consumes: ["multipart/form-data"],
    };

    return {
      schema: finalSchema as FastifySchema,
      url,
    };
  }

  // Para rotas não form-data, usa o transform padrão do Zod
  const result = jsonSchemaTransform({ schema, url });
  return result as TransformResult;
};
