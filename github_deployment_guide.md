# Deployment Guide: Bosa Addis to GitHub

This guide outlines the steps to push your local changes and files to your existing GitHub repository.

## Prerequisites
- You have already initialized Git in the folder.
- You have already linked the remote repository: `https://github.com/WorkuFikadu/ifa-bula-kebele-management.git`

## Step 1: Save (Commit) Your Changes
First, you need to stage the modified files and commit them to your local repository history.

1. Open a terminal (PowerShell or CMD) in `c:\xampp\htdocs\Bosa Addis`.
2. Add the modified files:
   ```bash
   git add modules/justice/police_print.php modules/justice/milisha_print.php modules/justice/gachana_print.php
   ```
   *(Optional: If you want to add everything, including new images/uploads, use `git add .`)*

3. Create the commit:
   ```bash
   git commit -m "Updated security ID card logos for Police, Milisha, and Gachana"
   ```

## Step 2: Push to GitHub
Now, upload your local commits to the online repository.

1. Ensure you are on the `main` branch (usually default):
   ```bash
   git push origin main
   ```

## Step 3: Handle Security (Optional but Recommended)
If you have sensitive database configuration (like passwords) in `config/database.php`, ensure it is either ignored via `.gitignore` or uses environment variables before pushing to a public repository.

---

> [!TIP]
> If you get an error saying "rejected ... (fetch first)", it means there are updates on GitHub that you don't have locally. Run:
> `git pull origin main --rebase`
> then try the `git push` command again.
