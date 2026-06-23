FROM node:22-alpine

WORKDIR /app

COPY apps/web/package.json apps/web/pnpm-lock.yaml* apps/web/.npmrc* ./

RUN corepack enable pnpm && pnpm install --frozen-lockfile --ignore-scripts

COPY apps/web/ .

EXPOSE 3000

CMD ["./node_modules/.bin/next", "dev"]
