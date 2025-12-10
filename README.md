# FixPre - AI Presentation Analyst 🚀

FixPre is a full-stack web application that helps students and professionals improve their PowerPoint presentations. Users upload `.pptx` files, and the system uses Google's Gemini AI to analyze the content, structure, and clarity of each slide, providing actionable feedback and a quality score.

![Project Status](https://img.shields.io/badge/status-active-success.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## 🌟 Key Features

* **Multimodal Analysis**: Extracts text and structure from `.pptx` files using `PHPOffice` and analyzes them using **Google Gemini 2.5 Flash**.
* **Asynchronous Processing**: Uses **Symfony Messenger** and **Redis** to handle heavy file processing in the background without blocking the UI.
* **Smart Dashboard**: Tracks user progress with interactive charts comparing personal average scores against the global platform average.
* **Secure Authentication**: Full auth system including Login, Registration, **Google OAuth**, and "Forgot Password" email workflows via JWT.
* **Robust Uploads**: Drag-and-drop interface with frontend validation (20MB limit) and secure backend handling.

## 🛠 Tech Stack

### Frontend
* **Framework**: Vue.js 3 (Composition API)
* **Styling**: Tailwind CSS
* **State Management**: Vue Composables (`useAuth`, `useFeedback`)
* **Router**: Vue Router
* **Build Tool**: Vite

### Backend
* **Framework**: Symfony 7 (PHP 8.2)
* **API**: API Platform (REST)
* **Database**: MySQL 8
* **ORM**: Doctrine ORM
* **Queue/Cache**: Redis
* **AI Engine**: Google Gemini API (`gemini-2.5-flash`)
* **Parsing**: PHPOffice/PHPPresentation

### Infrastructure
* **Containerization**: Docker & Docker Compose
* **Web Server**: Nginx
* **Worker**: PHP CLI (Supervisor/Docker)

---

## 📂 Project Architecture


The project is decoupled into two main parts running in separate containers:

```mermaid
graph LR
    User[User Browser] -- HTTPS --> Nginx[Nginx Proxy]
    Nginx -- /api --> Symfony[Symfony API]
    Nginx -- / --> Vue[Vue.js Frontend]
    Symfony -- Persist --> MySQL[(MySQL DB)]
    Symfony -- Dispatch --> Redis[(Redis Queue)]
    Worker[PHP Worker] -- Consume --> Redis
    Worker -- Extract --> PPTX[Presentation File]
    Worker -- Analyze --> Gemini[Google Gemini AI]
```
## Getting Started
### Prerequisites
Docker & Docker Compose installed.

Google Cloud Console Project (for OAuth & Gemini API).

1. Clone the Repository
```Bash

git clone [https://github.com/yourusername/fixpre.git](https://github.com/usmonov-r/fixpre.git)
```
2. Environment Configuration
Create a .env.local file in the root (or modify .env inside the php container logic) with your credentials:

``` Code snippet

# Database
DATABASE_URL="mysql://root:root@db:3306/fixpre?serverVersion=8.0.32"

# Mailer (Gmail or Mailtrap)
MAILER_DSN=smtp://your_email:your_password@smtp.gmail.com:587

# Google Gemini AI
GEMINI_API_KEY=your_gemini_api_key_here

# Google OAuth (Social Login)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# CORS (Allow your frontend domain)
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```
* **3. Build and Start Containers**
```Bash

docker compose up -d --build
```
* **4. Database Setup**: 
Once the containers are running, execute the migrations inside the PHP container:

``` Bash

docker compose exec php php bin/console doctrine:migrations:migrate
```
* **5. Start the Worker (Crucial)**: 
The worker processes the files. It handles the "heavy lifting" so the web server doesn't crash.
```bash
docker compose logs -f php-worker
```
## 📝 Usage Guide

### Register/Login
- Create an account via email or sign in with Google.

### Upload
- Go to **New Analysis**.
- Drag & drop your `.pptx` file (Max **20MB**).

### Wait for Processing
- Your file is queued.
- The frontend polls the status **every 2 seconds**.
- **Note:** Processing intentionally sleeps **4 seconds per slide** (Gemini Free Tier limit).

### View Results
- Once complete, see your **Overall Score** and **slide-by-slide feedback**.

### Dashboard
- Check your **history** and compare your scores with the **community average**.

> **Note:** The worker is configured to restart automatically in `docker-compose.yml`.

## 🔧 Troubleshooting

### 1. "Quota Exceeded" (429 Error)
If you see a **Gemini 429** error, the worker is processing slides too fast for the free tier.

**Fix:**
- Ensure the `sleep(4)` line exists in `ProcessPresentationHandler.php`.
- Ensure you are using `models/gemini-2.5-flash` in `GeminiFeedbackService.php`.

---

### 2. "File Too Large"
**Frontend:**
- Checks if file > **20MB**.

**Backend:**
- Nginx: `client_max_body_size` is set to **25MB**.
- PHP: `upload_max_filesize` is set to **25MB** in docker config.

### 3. Worker Memory Exhausted
If the worker crashes with **"Allowed memory size exhausted"**:

The `php-worker` container is configured with:

`memory_limit = 512M`

Ensure your `custom.ini` is mounted correctly in `docker-compose.yml`.

## 🧪 Testing

**Run Backend Test Suite:**

```bash
docker compose exec php php bin/phpunit
```
To test the worker manually:

```Bash

docker compose exec php php bin/console messenger:consume async -vv
```

📄 License
This project is licensed under the MIT License.
