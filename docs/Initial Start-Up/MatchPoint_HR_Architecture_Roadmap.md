# MatchPoint HR — AI-Powered Candidate Intelligence & Interview Orchestrator

## 1. Product Concept Overview
MatchPoint HR is an advanced Applicant Tracking System (ATS) extension. It ingests raw candidate resumes, utilizes Named Entity Recognition (NER) and vector search to score candidates against job descriptions, and dramatically reduces recruiter workload. Furthermore, it automatically designs tailored, multi-round interview pipelines based on the candidate's specific background and schedules these rounds dynamically.

## 2. System Architecture

### Architectural Pattern: RAG-Enhanced B2B SaaS
The system utilizes a hybrid relational and vector database approach. Relational data handles state and scheduling, while the vector database handles semantic matching.

```text
[ Recruiter Dashboard ] ◄──────┐
        │                      │ (WebSockets / Polling for Updates)
        ▼                      │
[ Core SaaS API (Laravel / Django) ] ──> [ MySQL / PostgreSQL ] (Users, Jobs, Schedules)
        │
        │ (REST API / Async Task)
        ▼
[ AI Candidate Engine (FastAPI) ]
 ├── Document Parser (PDF/DOCX to Markdown)
 ├── NER & Chunking Engine (spaCy / LangChain)
 ├── Vector Embeddings Model (SentenceTransformers)
 ├── LLM Prompt Chainer (Interview Question Generation)
        │
        ├──> [ Qdrant / pgvector ] (Vector DB for Semantic Resume Matching)
        └──> [ Calendar API Integration ] (Google Calendar / Outlook for Scheduling)
```

## 3. Technology Stack

* **Frontend:** Interactive dashboard for Kanban-style candidate tracking.
* **Core API:** Laravel (PHP) OR Django (Python). Handles standard CRUD, multi-tenancy, and email/calendar triggers.
* **AI Microservice:** FastAPI (Python). 
* **Data Layer:** MySQL (Relational), Qdrant (Vector Database for similarity search).
* **AI/ML Tools:** LLM (OpenAI/Anthropic) for question generation, PyPDF/Unstructured.io for parsing.
* **Integrations:** Google Calendar/Microsoft Graph APIs for interview scheduling.

## 4. Core Functionality & Features

1. **Intelligent Resume Parsing:** Extracts skills, tenure, and project summaries into a structured JSON schema.
2. **Bi-Encoder Match Scoring:** Converts the job description and resume into vector embeddings. Calculates cosine similarity to give an objective 0-100% match score, highlighting exact skill gaps.
3. **Automated Multi-Stage Interview Generator:** Based on the resume's claims and the job description, the LLM generates a tailored interview packet:
    * **Screening Round:** Basic culture fit and high-level experience verification.
    * **Technical/Theory Round:** Deep-dive questions specifically targeting the tech stack mentioned in the resume (e.g., "You mentioned caching in Redis; how did you handle cache invalidation?").
    * **Practical/Desktop Round:** A custom micro-assignment or pair-programming prompt based on the candidate's reported skill level.
    * **HR/Behavioral Assessment:** Situational questions targeting leadership or teamwork, incorporating behavioral psychology tricks (e.g., STAR method prompts).
4. **Smart Interview Scheduling:** Connects to recruiter calendars, finds mutual availability slots, and automatically dispatches calendar invites with the generated interview structure attached as private notes for the interviewer.

## 5. Development Roadmap

### Phase 1: ATS Foundation & Parsing
* Build the core relational database (Jobs, Candidates, Applications).
* Develop the FastAPI endpoint to accept PDF uploads, parse the text, and return structured JSON (using OpenAI structured outputs or native NER).

### Phase 2: Vector Search & Match Scoring
* Integrate Qdrant or `pgvector`.
* Generate embeddings for Job Descriptions and Candidate Resumes.
* Create the matching algorithm to return top candidates for a role with highlighted "missing skills."

### Phase 3: The AI Interview Chainer
* Build a sequential LLM prompt chain.
* Pass the structured resume data and job description through 4 distinct prompts (Screening, Theory, Practical, HR).
* Format the output into a clean, downloadable PDF or interactive web view for the interviewers.

### Phase 4: Scheduling & DevOps
* Integrate Google Calendar OAuth for recruiters.
* Build the auto-scheduling logic based on availability.
* Containerize the application using Docker and write GitHub Actions for automated deployment.

## 6. Engineering Showcase Value
* **Complex Data Orchestration:** Merging relational logic (scheduling, users) with vector math (similarity scoring) and generative AI (dynamic content).
* **Workflow Automation:** Replicating a real-world enterprise workflow (parsing -> scoring -> testing -> scheduling).
* **Advanced Prompt Engineering:** Creating reliable, structured multi-shot prompts that generate highly specific, technical interview content without hallucinating.
