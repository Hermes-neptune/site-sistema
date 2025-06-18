-- DropForeignKey
ALTER TABLE "creditos" DROP CONSTRAINT "creditos_username_fkey";

-- DropIndex
DROP INDEX "creditos_username_key";

-- AddForeignKey
ALTER TABLE "creditos" ADD CONSTRAINT "creditos_username_fkey" FOREIGN KEY ("username") REFERENCES "users"("username") ON DELETE CASCADE ON UPDATE CASCADE;
