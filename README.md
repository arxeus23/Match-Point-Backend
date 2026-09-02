# MatchPoint HR

MatchPoint HR is a multi-tenant, AI-powered applicant tracking system built with Laravel, React, PostgreSQL with pgvector, Redis, and Docker.

## Local development

Keep the frontend repository at `D:\MyDownloads\Match-Point-Frontend`. Docker Desktop is the only runtime requirement.

From this repository, run:

```powershell
.\docker-start.ps1
```

The launcher validates Docker, builds images, starts PostgreSQL and Redis, runs Laravel migrations, and starts the API, queue worker, and frontend.

Run in the background:

```powershell
.\docker-start.ps1 -Detached
```

Skip rebuilding existing images:

```powershell
.\docker-start.ps1 -Detached -NoBuild
```

Services:

- Frontend: http://localhost:3000
- Laravel API: http://localhost:8000
- Health check: http://localhost:8000/up

Stop everything with `docker compose down`. PostgreSQL and Redis data are stored in named volumes and survive normal container restarts.
