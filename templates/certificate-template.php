<style>

@page{
size:A4 landscape;
margin:6mm;
}

body{
margin:0;
font-family: DejaVu Serif, serif;
text-align:center;
}

.wrapper{
width:100%;
}

.certificate{

padding:38mm;

border:3mm solid #2f6edb;

background:#f5f8ff;

box-sizing:border-box;

}

.logo img{
height:42px;
}

.title{
font-size:32px;
color:#2f6edb;
font-weight:bold;
margin-top:4px;
}

.subtitle{
font-size:14px;
margin-top:4px;
}

.name{
font-size:26px;
font-weight:bold;
margin:10px 0;
}

.course{
font-size:18px;
color:#2f6edb;
}

.info{
width:100%;
margin-top:14px;
font-size:12px;
}

.info td{
padding:3px;
}

.signature{
margin-top:18px;
}

.signature img{
height:40px;
}

.footer{
margin-top:6px;
font-size:10px;
}

</style>



<div class="wrapper">

<div class="certificate">

<div class="logo">
<img src="../assets/logo.png">
</div>

<div class="title">
CERTIFICATE OF COMPLETION
</div>

<div class="subtitle">
This certificate is proudly presented to
</div>

<div class="name">
<?= $title ?> <?= $data['name'] ?>
</div>

<div class="subtitle">
for successfully completing the course
</div>

<div class="course">
<?= $data['course'] ?>
</div>


<table class="info">

<tr>
<td><b>Mentor:</b> <?= $data['mentor'] ?></td>
<td align="right"><b>Grade:</b> <?= $data['grade'] ?></td>
</tr>

<tr>
<td><b>Registration:</b> <?= $data['registration_number'] ?></td>
<td align="right"><b>Certificate:</b> <?= $data['certificate_number'] ?></td>
</tr>

<tr>
<td colspan="2" align="center">
<b>Duration:</b> <?= $data['start_date'] ?> – <?= $data['end_date'] ?>
</td>
</tr>

</table>


<div class="signature">

<img src="../assets/signature.png">

<br>

<b>Exam Controller</b>

</div>


<div class="footer">

This certificate verifies that the above named individual has successfully completed the course requirements.

</div>

</div>

</div>