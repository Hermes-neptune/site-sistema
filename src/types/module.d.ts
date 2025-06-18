declare namespace NodeJS {
  export interface ProcessEnv {
    JWT_SECRET_KEY: string;

    SUPABASE_BUCKET: string;
    SUPABASE_URI: string;
    SUPABASE_KEY: string;
  }
}
