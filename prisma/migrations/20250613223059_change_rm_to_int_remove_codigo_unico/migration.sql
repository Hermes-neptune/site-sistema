/*
  Warnings:

  - You are about to drop the column `codigo_unico` on the `users` table. All the data in the column will be lost.
  - Changed the type of `rm` on the `users` table. No cast exists, the column would be dropped and recreated, which cannot be done if there is data, since the column is required.

*/
-- DropIndex
DROP INDEX "users_codigo_unico_key";

-- AlterTable
ALTER TABLE "users" DROP COLUMN "codigo_unico",
DROP COLUMN "rm",
ADD COLUMN     "rm" INTEGER NOT NULL;

-- CreateIndex
CREATE UNIQUE INDEX "users_rm_key" ON "users"("rm");
