import { prisma } from "../lib/prisma";

export async function gerarcodigo() {
  let codigo;
  let exists;

  do {
    codigo = Math.floor(Math.random() * 2_000_000) + 1;
    exists = await prisma.users.findFirst({
      where: { codigo_unico: codigo },
    });
  } while (exists);

  return codigo;
}
