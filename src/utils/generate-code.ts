import { prisma } from "../lib/prisma";

export async function gerarcodigo() {
  let codigo;
  let exists;

  do {
    codigo = Math.floor(Math.random() * 900000) + 100000;

    exists = await prisma.users.findFirst({
      where: { rm: codigo },
    });
  } while (exists);

  return codigo;
}
