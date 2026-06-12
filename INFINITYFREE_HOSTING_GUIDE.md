# InfinityFree Hosting Guide: Bosa Addis Kebele System

Follow these steps to deploy your project from local XAMPP to a live web server on InfinityFree.

## 1. Prepare your Database
1. Log into your InfinityFree Client Area and go to **Control Panel**.
2. Click **MySQL Databases**.
3. Create a new database.
4. **IMPORTANT**: Note down your **DB Hostname**, **DB Name**, and **DB Username**. 
5. Go back to Home and click **phpMyAdmin**.
6. Select your new database and click **Import**.
7. Upload your `schema.sql` or the latest database dump from your local machine.

## 2. Upload Files via FTP
Using the Online File Manager is slow for many files. It is better to use **FileZilla**:
1. Download [FileZilla](https://filezilla-project.org/).
2. Get your **FTP Details** from the InfinityFree Client Area (FTP Hostname, FTP Username, FTP Password).
3. Connect and open the **`htdocs`** folder on the remote server.
4. Drag and drop everything from `c:\xampp\htdocs\Bosa Addis\` into the `htdocs` folder.

## 3. Configure Database Connection
Once the files are uploaded, you must update the database credentials to match the remote server.
1. Open the File Manager (or use FileZilla).
2. Navigate to `config/database.php`.
3. Edit the file and replace your local `localhost` settings with the ones provided by InfinityFree:

```php
<?php
// config/database.php (Remote Version)
return [
    'host' => 'sqlXXX.infinityfree.com', // Replace with your MySQL Hostname
    'user' => 'epiz_XXXXXXXX',            // Replace with your MySQL Username
    'pass' => 'your_password',           // Replace with your Account Password
    'dbname' => 'epiz_XXX_your_db_name'  // Replace with your Database Name
];
?>
```

## 4. Fix File Permissions
If images aren't uploading on the live site:
1. Ensure the `uploads/` directory has **777** or **755** permissions in your FTP client.

## 5. View your Site
Your site will be live at: `http://your-subdomain.infinityfreeapp.com`

---
> [!NOTE]
> If you encounter a "403 Forbidden" error, make sure your browser is pointing to `index.php` in the root of the `htdocs` folder.
