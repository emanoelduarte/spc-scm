<div class="container text-center mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-body p-5">

            <i class="fa-solid <?= $this->data['errorIcon'] ?> fa-3x <?= $this->data['errorColor'] ?> mb-3"></i>

            <h4 class="mt-2"><?= $this->data['errorTitle'] ?></h4>
            <p class="text-muted"><?= $this->data['errorMessage'] ?></p>

            <hr>

            <small class="text-muted">
                Precisa de ajuda? 
                <a href="mailto:<?= $this->data['emailAdm'] ?>"><?= $this->data['emailAdm'] ?></a>
            </small>

            <div class="mt-3">
                <a href="<?= $_ENV['URL_ADM'] ?>dashboard" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-house"></i> Voltar ao início
                </a>
            </div>

        </div>
    </div>
</div>