# E-Learning Platform
Aplicație de e-learning cu gestiune completă a cursurilor online și prezențiale. Include gestionare cursuri, traineri, studenți, planificare sesiuni, lecții, conținut video, fișiere și teste online cu diferite tipuri de întrebări.

# Versiune 2.0

# Data 20.08.2025

## Generale
Font Awesome
Simple-editor (WYSIWYG)
Foundation 6 (framework CSS)

## Specifice
1. PHPMailer - trimitere emailuri de confirmare înscrieri
2. Sistem video - încărcare și afișare fișiere video pentru lecții
3. Upload fișiere - materiale suport pentru studenți
4. Teste online - quiz cu întrebări simple/multiple/deschise

## Roluri și Permisiuni

### 1. Admin
- Gestionare categorii cursuri
- Gestionare cursuri (CRUD complet)
- Gestionare traineri
- Gestionare planificare cursuri (schedules)
- Gestionare înscrieri (enrollments)
- Gestionare studenți
- Acces la toate rapoartele

### 2. Trainer
- Vizualizare cursuri alocate
- Gestionare lecții (lessons)
- Încărcare conținut video
- Încărcare fișiere suport
- Creare și gestionare teste online
- Vizualizare studenți înscriși
- Corectare teste

### 3. Student
- Vizualizare cursuri disponibile
- Înscriere la cursuri
- Acces la lecții
- Vizualizare conținut video
- Descărcare materiale suport
- Susținere teste online
- Vizualizare rezultate

## Module Principale

### Public
- **index.php** - listare cursuri disponibile pe categorii
- **courses_template.php** - detalii curs individual
- **calendar.php** - calendar cursuri online și prezențiale
- **enrollment.php** - formular înscriere la curs

### Administrativ
- **sitecourses.php** - gestiune cursuri (15+ câmpuri: nume, preț, discount, descriere, obiective, public țintă, categorie, autor, imagine, keywords, metadescription, etc.)
- **sitecoursecategories.php** - gestiune categorii cursuri
- **sitecourseschedules.php** - planificare sesiuni (date start/end/examen, ore, detalii, locație)
- **sitetrainers.php** - gestiune traineri (nume, poză, prezentare scurtă/lungă, CV, certificări)
- **sitestudents.php** - gestiune conturi studenți (activare/dezactivare)
- **siteenrollments.php** - gestiune înscrieri (aprobare, respingere, activare/dezactivare)

### Conținut Educațional
- **sitelessons.php** - gestiune lecții pentru fiecare curs
- **sitevideos.php** - upload și gestiune fișiere video pentru lecții
- **sitefiles.php** - upload și gestiune fișiere suport (PDF, DOC, XLS, etc.)
- **sitetests.php** - creare și gestiune teste online
- **sitequestions.php** - gestiune întrebări test (simple, multiple choice, deschise)

## Tipuri de Cursuri
1. **Online (E-learning)** - cursuri complet online, accesibile oricând
2. **Prezențial (On-site)** - cursuri față în față cu planificare
3. **Hibrid** - combinație online + prezențial

## Funcționalități Teste

### Tipuri Întrebări
1. **Simple choice** - o singură opțiune corectă
2. **Multiple choice** - mai multe opțiuni corecte
3. **Întrebări deschise** - răspuns text liber

### Gestiune Teste
- Creare baterii de întrebări
- Randomizare întrebări
- Limită de timp
- Punctaj pe întrebare
- Corectare automată (choice) / manuală (deschise)
- Afișare rezultate și feedback

## Fișiere Cheie

### Public
- index.php
- courses_template.php
- calendar.php
- enrollment.php
- createaccount.php

### Admin - Gestiune Structură
- sitecourses.php
- sitecoursecategories.php
- sitecourseschedules.php
- sitetrainers.php
- sitestudents.php
- siteenrollments.php

### Admin - Gestiune Conținut
- sitelessons.php
- sitevideos.php
- sitefiles.php
- sitetests.php
- sitequestions.php

### Trainer
- mylessons.php
- uploadvideos.php
- uploadfiles.php
- createtest.php
- grading.php

### Student
- mycourses.php
- viewlesson.php
- taketest.php
- myresults.php

## Dependințe
1. PHPMailer - trimitere emailuri
2. Foundation 6 - framework CSS
3. Font Awesome - iconuri
4. Simple-editor - editor WYSIWYG
5. HTML5 Video Player - redare video

## Baza de Date

### Tabele Principale
- **elearning_courses** - cursuri
- **elearning_coursecategories** - categorii cursuri
- **elearning_courseschedules** - planificare sesiuni
- **elearning_trainers** - traineri
- **elearning_enrollments** - înscrieri studenți
- **elearning_lessons** - lecții
- **elearning_videos** - fișiere video
- **elearning_files** - fișiere suport
- **elearning_tests** - teste online
- **elearning_questions** - întrebări teste
- **elearning_answers** - răspunsuri studenți
- **elearning_results** - rezultate teste

## Securitate (Actualizat 2025)
✅ Toate query-urile folosesc MySQLi prepared statements
✅ XSS prevention cu htmlspecialchars() pentru toate output-urile
✅ Input sanitization cu (int) casting și mysqli_real_escape_string()
✅ Session management pentru autentificare
✅ Role-based access control (Admin/Trainer/Student)

## Integrări
1. **E-Factura** - facturare automată înscrieri (opțional)
2. **Email notifications** - confirmare înscriere, reminder teste, rezultate
3. **Calendar sistem** - sincronizare cu calendar.php

## To Do
1. Certificare automată la finalizare curs
2. Progres tracking per student
3. Gamification - badges, puncte, clasament
4. Forum/chat între studenți
5. Live streaming pentru cursuri online
6. Integrare Zoom/Teams pentru sesiuni live
7. Export rezultate în Excel/PDF
8. Rapoarte avansate trainer/admin
9. Notificări push/email automate
10. Mobile app (iOS/Android)
