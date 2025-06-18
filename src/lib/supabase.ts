import { createClient } from "@supabase/supabase-js";
import "dotenv/config";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_KEY;

if (!supabaseUrl) {
  throw new Error("Environment variable SUPABASE_URL is required.");
}
if (!supabaseKey) {
  throw new Error("Environment variable SUPABASE_SERVICE_KEY is required.");
}

export const supabaseClient = createClient(supabaseUrl, supabaseKey, {
  auth: {
    persistSession: false,
  },
});
