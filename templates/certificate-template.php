<style>

body{
text-align:center;
font-family:serif;
}

h1{
font-size:40px;
}

.name{
font-size:32px;
margin:20px;
}

</style>

<h1>Certificate of Completion</h1>

<p>This certifies that</p>

<div class="name">

<?= $title ?> <?= $data['name'] ?>

</div>

<p>has completed</p>

<h2><?= $data['course'] ?></h2>

<p>Mentor: <?= $data['mentor'] ?></p>

<p>Registration: <?= $data['registration_number'] ?></p>

<p>Certificate: <?= $data['certificate_number'] ?></p>

<p>Duration: <?= $data['start_date'] ?> - <?= $data['end_date'] ?></p>

<p>Grade: <?= $data['grade'] ?></p>