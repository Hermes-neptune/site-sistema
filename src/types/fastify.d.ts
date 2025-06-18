import "@fastify/jwt";

declare module "fastify" {
  interface FastifyRequest {
    jwtVerify(): Promise<void>;
    user: {
      id: string;
      username: string;
    };
  }

  interface FastifyReply {
    jwtSign(payload: object): Promise<string>;
  }

  supabaseService: SupabaseService;
}

declare module "fastify" {
  export interface FastifyInstance {
    supabaseService: SupabaseService;
    authenticate: (
      request: FastifyRequest,
      reply: FastifyReply
    ) => Promise<void>;
  }

  interface RouteShorthandOptions {
    swagger?: {
      [key: string]: unknown;
    };
  }
}
