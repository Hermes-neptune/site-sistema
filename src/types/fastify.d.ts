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
}
