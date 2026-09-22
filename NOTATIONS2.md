###MIGRATIONS E SEEDS

Criar as migrations referente ao módulo de orçamento de obra

'DdmsDamanBudgets', 'AdmsDamanStages', 'AdmsDamanBudgetStages', 'AdmsDamanBudgetItems', 'AdmsDamanOrderItemAllocations'
[[EXECUTA]]

Cria as Seeds referente ao módulo de orçamento de obra

'AddAdmsDamanBudgetsSeeder', 'AddAdmsDamanBudgetStagesSeeder', 'AddAdmsDamanBudgetItemsSeeder', 'AddAdmsDamanOrderItemAllocationsSeeder'
[[EXECUTA]]

Criar os grupos e páginas referente ao 'budgets'

Criar diretório de controller 'budgets'
Criar controller 'ListBudgets' -> view
Criar o script_budgets.js
Criar controller 'CreateBudget' - view

Criar item de Menu para orçamentos

Criar BudgetRepository
StageRepository
Criar View da Controle ListBudgets, alterar arquivo de menu LayoutPageService, 
atualizar o arquivo main, incluindo o novo script de budgets
Criar BudgetStageRepository

/------>
Consultar Notas emitidas para a empresa
Fazer a migration - > vendor/bin/phinx create AdmsDamanNfes -c database/phinx.php
vendor/bin/phinx create AddCheckedToAdmsDamanNfes -c database/phinx.php
Criar variáveis de ambiente do caminho do certificado e do cnpj da empresa
Fazer a migration -> vendor/bin/phinx create CreateAdmsDamanNfeSyncTable -c database/phinx.php

---------------->
PurchaseDocumentsRepository.php
PurchaseInstallmentsRepository.php
modificar o PaymentMethodsRepository
Criar migrations
