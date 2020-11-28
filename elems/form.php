<div class="row">
  <div class="col-lg-4 mx-auto mt-3">
    <div class="alert alert-success">
      <?php echo $_SESSION['message'] = $_SESSION['message'] ?? 'Поле для вывода информационных сообщений'; ?>
    </div>

    <h3 style="text-align: center;">Заполните форму:</h3>

    <form method="post" action="">
      <div class="form-group">
        <label for="formName">ФИО (обязательно):</label>
        <input type="text" name="name" class="form-control" id="formName"
        value="<?php echo isset($_SESSION['error']) && $_SESSION['error'] ? $name : ''; ?>">
      </div>
      <div class="form-group">
        <label for="formEmail">email (кроме домена gmail):</label>
        <input type="email" name="email" class="form-control" id="formEmail"
        value="<?php echo isset($_SESSION['error']) && $_SESSION['error'] ? $email : ''; ?>">
      </div>
      <div class="form-group">
        <label for="formTelefon">мобильный телефон (обязательно):</label>
        <input type="text" name="telefon" class="form-control" id="formTelefon"
        value="<?php echo isset($_SESSION['error']) && $_SESSION['error'] ? $telefon : ''; ?>">
      </div>
      <div class="form-group">
        <label for="formComment">комментарий:</label>
        <textarea name="comment" rows="4" cols="80" class="form-control" id="formComment"><?php
          echo isset($_SESSION['error']) && $_SESSION['error'] ? $comment : '';
        ?></textarea>
      </div>
      <button type="submit" name="submit" class="btn btn-success">Отправить</button>
    </form>
    <?php
    // если в сессии есть сообщения 'message' - очистим ее
    if (isset($_SESSION['message'])) {
      unset($_SESSION['message']);
    }

    // если в сессии есть сообщения 'error' - очистим ее
    if (isset($_SESSION['error'])) {
      unset($_SESSION['error']);
    }
    ?>
  </div>
</div>
