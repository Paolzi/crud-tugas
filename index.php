<?php
// Konfigurasi Database Azure
$host = getenv('DB_HOST') ?: 'db-praktikum-syaidan.mysql.database.azure.com';
$user = getenv('DB_USER') ?: 'syaidanadmin';
$pass = getenv('DB_PASS') ?: 'password';
$db   = getenv('DB_NAME') ?: 'db_mahasiswa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS mahasiswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        nim VARCHAR(20) NOT NULL,
        jurusan VARCHAR(50) NOT NULL
    )");
} catch(PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// 1. CREATE (Tambah Data)
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO mahasiswa (nama, nim, jurusan) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nama'], $_POST['nim'], $_POST['jurusan']]);
    header("Location: index.php");
    exit;
}

// 2. UPDATE (Ubah Data)
if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE mahasiswa SET nama = ?, nim = ?, jurusan = ? WHERE id = ?");
    $stmt->execute([$_POST['nama'], $_POST['nim'], $_POST['jurusan'], $_POST['id']]);
    header("Location: index.php");
    exit;
}

// 3. DELETE (Hapus Data)
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php");
    exit;
}

// Ambil Data untuk Form Edit jika parameter 'edit' ada di URL
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. READ (Ambil Semua Data)
$mahasiswa = $pdo->query("SELECT * FROM mahasiswa ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi CRUD Mahasiswa - Azure App Service</title>
    <style>
        body { font-family: sans-serif; margin: 40px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #0078d4; color: white; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input[type="text"] { padding: 8px; margin-right: 10px; width: 25%; }
        button { padding: 8px 16px; background: #0078d4; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-edit { color: #0078d4; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-delete { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Aplikasi CRUD Data Mahasiswa (Azure App Service + MySQL)</h2>
    
    <!-- Form Create / Update -->
    <form action="index.php" method="POST">
        <h3><?= $edit_data ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa' ?></h3>
        
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        
        <input type="text" name="nama" placeholder="Nama Mahasiswa" value="<?= $edit_data ? htmlspecialchars($edit_data['nama']) : '' ?>" required>
        <input type="text" name="nim" placeholder="NIM" value="<?= $edit_data ? htmlspecialchars($edit_data['nim']) : '' ?>" required>
        <input type="text" name="jurusan" placeholder="Jurusan" value="<?= $edit_data ? htmlspecialchars($edit_data['jurusan']) : '' ?>" required>
        
        <?php if ($edit_data): ?>
            <button type="submit" name="update">Update</button>
            <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #555;">Batal</a>
        <?php else: ?>
            <button type="submit" name="add">Simpan</button>
        <?php endif; ?>
    </form>

    <!-- Table Read, Update Link, & Delete -->
    <h3>Daftar Mahasiswa</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Jurusan</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($mahasiswa as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['nim']) ?></td>
            <td><?= htmlspecialchars($row['jurusan']) ?></td>
            <td>
                <a class="btn-edit" href="index.php?edit=<?= $row['id'] ?>">Edit</a>
                <a class="btn-delete" href="index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus data?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>