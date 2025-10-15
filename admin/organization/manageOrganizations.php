<?php
// admin/organization/manageOrganizations.php
include __DIR__ . '/../db.php'; // provides $conn and $baseURL
session_start();

$res = $conn->query("SELECT o.*, y_users.name AS creator_name FROM organizations o LEFT JOIN y_users ON o.created_by = y_users.id ORDER BY created_at DESC");
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div id="layoutSidenav_content">
  <main class="container-fluid px-4 mt-4">
    <h2>Organizations</h2>
    <a href="<?= $baseURL ?>organization/createOrganization.php" class="btn btn-primary mb-3">Add Organization</a>

    <?php if ($res && $res->num_rows): ?>
      <table class="table table-bordered">
        <thead><tr><th>Name</th><th>Contact</th><th>Location</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['contact_email'] . ' / ' . $row['contact_phone']) ?></td>
            <td><?= htmlspecialchars($row['continent'].' / '.$row['country'].' / '.$row['state'].' / '.$row['city']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
              <a class="btn btn-sm btn-info" href="<?= $baseURL ?>organization/viewOrganization.php?id=<?= $row['id'] ?>">View</a>
              <a class="btn btn-sm btn-warning" href="<?= $baseURL ?>organization/editOrganization.php?id=<?= $row['id'] ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="<?= $baseURL ?>organization/deleteOrganization.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this org?')">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No organizations yet.</p>
    <?php endif; ?>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
