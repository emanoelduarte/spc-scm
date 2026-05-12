<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AddAdmsDamanSuppliers extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {

        // Variável para receber os dados a serem inseridos
        $data = [];

        // Variável para receber os dados que devem ser validados antes de cadastrar
        $suppliers = [
            [
                'legal_name' => 'CASA COMÉRCIO DE MATERIAIS DE CONSTRUÇÃO LTDA',
                'trade_name' => 'DICASA',
                'cnpj' => '07013648000818',
                'contact_name' => 'JUNIOR',
                'phone' => '91985044131',
                'adms_daman_supplier_addresses_id' => 1, 
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'BELTUBO BELEM TUBO COMERCIO LTDA',
                'trade_name' => 'BELTUBO',
                'cnpj' => '34891754000109',
                'contact_name' => 'DANILO',
                'phone' => '91988091401',
                'adms_daman_supplier_addresses_id' => 2, 
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'IMPORTADOR OPLIMA LTDA',
                'trade_name' => 'OPLIMA',
                'cnpj' => '04945481000169',
                'contact_name' => 'MESSIAS/EDILSON',
                'phone' => '91980963374',
                'adms_daman_supplier_addresses_id' => 3, 
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'DIAS COMERCIO DE COMPENSADOS LTDA',
                'trade_name' => 'DIGEMA',
                'cnpj' => '03318958000113',
                'contact_name' => 'CICERONE',
                'phone' => '91991348464',
                'adms_daman_supplier_addresses_id' => 4,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'ACO BELEM COMERCIAL LTDA',
                'trade_name' => 'ACO BELÉM',
                'cnpj' => '04082321000133',
                'contact_name' => 'GLEYCE',
                'phone' => '91988506863',
                'adms_daman_supplier_addresses_id' => 5,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'DISMONZA TINTAS PA LTDA',
                'trade_name' => 'DISMONZA',
                'cnpj' => '27878827000159',
                'contact_name' => 'ADILSON',
                'phone' => '91996144281',
                'adms_daman_supplier_addresses_id' => 6,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'TECHFIX COM. DE PRODUTOS DE FIXAÇÃO LTDA',
                'trade_name' => 'TECHFIX',
                'cnpj' => '07084548000106',
                'contact_name' => 'IZAIAS',
                'phone' => '91981775236',
                'adms_daman_supplier_addresses_id' => 7,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'INDUSTRIA DRYKO LTDA',
                'trade_name' => 'DRYKO',
                'cnpj' => '03081895000558',
                'contact_name' => 'PAULO MOREIRA',
                'phone' => '91980966802',
                'adms_daman_supplier_addresses_id' => 8,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'AMAZON COMERCIO DE MATERIAL ELETRICO LTDA',
                'trade_name' => 'AMAZON ELETRON',
                'cnpj' => '33145796000120',
                'contact_name' => 'EDGAR LUCAS',
                'phone' => '91984665206',
                'adms_daman_supplier_addresses_id' => 9,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'MATTOS & MATTOS COMERCIO DE TINTAS E SERVIÇOS LTDA',
                'trade_name' => 'DECORCOLORS',
                'cnpj' => '56063994000140',
                'contact_name' => 'JUNIOR',
                'phone' => '91986012668',
                'adms_daman_supplier_addresses_id' => 10,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'FRIGELAR COMERCIO E INDUSTRIA LTDA',
                'trade_name' => 'FRIGELAR',
                'cnpj' => '92660406003800',
                'contact_name' => 'ANDREIA',
                'phone' => '91982330390',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'S DE C LIMA COMERCIO DE MATERIAL DE CONSTRUCAO LTDA',
                'trade_name' => 'METASEG',
                'cnpj' => '35572764000136',
                'contact_name' => 'ELSA',
                'phone' => '91980707592',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'M&M MATERIAIS DE CONSTRUCAO LTDA',
                'trade_name' => 'MILANO',
                'cnpj' => '28718805000194',
                'contact_name' => 'MARCO',
                'phone' => '91981342276',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'PARAFERRO PRODUTOS METALÚRGICOS LTDA',
                'trade_name' => 'PARAFERRO',
                'cnpj' => '00911696000370',
                'contact_name' => 'AMANDA OLIVEIRA',
                'phone' => '91988111520',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'VIPE PRODUTOS E SERVICOS LTDA',
                'trade_name' => 'PLANETA ENERGIA',
                'cnpj' => '27100625000262',
                'contact_name' => 'ERICK',
                'phone' => '91993932345',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'PERMUTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'ARTECAL IND. DE ARTEFATOS CASTANHAL LTDA',
                'trade_name' => 'PRE NORTE',
                'cnpj' => '07204840000196',
                'contact_name' => 'MARCIO COELHO',
                'phone' => '91988144732',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'PREMAC INDUSTRIA E COMERCIO LTDA',
                'trade_name' => 'PREMAC',
                'cnpj' => '30655866000100',
                'contact_name' => 'ELTON',
                'phone' => '91988660636',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'CONTROLE IND. E COM. DE MAT. ELETR. LTDA',
                'trade_name' => 'ELETROTRANSOL',
                'cnpj' => '10489368000119',
                'contact_name' => 'ELIVELTON',
                'phone' => '91980375598',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'VERBRAS IND. E COM. DE TINTAS LTDA',
                'trade_name' => 'VERBRAS',
                'cnpj' => '0772703500025',
                'contact_name' => 'ALAN',
                'phone' => '91989230985',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'VOTORANTIM CIMENTOS NNE SA',
                'trade_name' => 'VOTORANTIM',
                'cnpj' => '10656452003529',
                'contact_name' => 'LUMA',
                'phone' => '91991620145',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'MARAJO COM. VAREJISTA DE MAT. DE CONTRUCAO LTDA',
                'trade_name' => 'MARAJO',
                'cnpj' => '42863070000113',
                'contact_name' => 'LAISA',
                'phone' => '91988433579',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'JURUNENSE HOME CENTER LTDA',
                'trade_name' => 'JURUNENSE HOME CENTER',
                'cnpj' => '13772792000407',
                'contact_name' => 'RAIMUNDA NONATA',
                'phone' => '91991359408',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'NUNES E REIS LOCACAO E COM. DE EQUIP. E MAQ. PARA CONST. LTDA',
                'trade_name' => 'LOC+',
                'cnpj' => '53439607000139',
                'contact_name' => 'BRUNO',
                'phone' => '91993611111',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 2,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'VERTICAL LOCACAO DE MAQUINAS E EQUIPAMENTOS LTDA',
                'trade_name' => 'VERTICAL',
                'cnpj' => '05689835000114',
                'contact_name' => 'RENAN',
                'phone' => '91985505660',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 2,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'PARA PAVER ARTEFATOS DE CONCRETO LTDA',
                'trade_name' => 'PARABLOCOS',
                'cnpj' => '51963850000126',
                'contact_name' => 'DAYANE',
                'phone' => '91992363730',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'CONSTRUTORA VIENA STAR LTDA',
                'trade_name' => 'CONSTAR',
                'cnpj' => '04028420000137',
                'contact_name' => 'NELSON',
                'phone' => '91999816878',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'IMPERSIK COMERCIO E SERVICOS LTDA',
                'trade_name' => 'IMPERSIK',
                'cnpj' => '34682732000120',
                'contact_name' => 'SOCORRO',
                'phone' => '91984173497',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'STUDIO GESSO SAL LTDA',
                'trade_name' => 'STUDIO GESSO SAL',
                'cnpj' => '54321644000100',
                'contact_name' => 'MARA',
                'phone' => '91981147824',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'ALEXSANDRO VENICIUS PEREIRA',
                'trade_name' => 'ALPEREIRA COMERCIO E REPRESENTACOES.',
                'cnpj' => '11542745000107',
                'contact_name' => 'ALEX PEREIRA',
                'phone' => '91993932345',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'HS CONFECCAO DE UNIFORMES LTDA',
                'trade_name' => 'H S CONFECCAO',
                'cnpj' => '83210658000155',
                'contact_name' => 'LAELSON',
                'phone' => '91992805350',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'INOVARE COMERCIO SERVICO E REPRESENTACAO LTDA',
                'trade_name' => 'INOVARE',
                'cnpj' => '05249622000171',
                'contact_name' => 'GABRIEL PINHEIRO',
                'phone' => '9133660700',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'BASE COMERCIO DE PRODUTOS CONSTRUTIVOS LTDA',
                'trade_name' => 'BASE PRODUTOS CONSTRUTIVOS',
                'cnpj' => '34650226000150',
                'contact_name' => 'LAURA',
                'phone' => '91982165696',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'RD REVESTIMENTO COMERCIO UNIPESSOAL LTDA',
                'trade_name' => 'RD REVESTIMENTO',
                'cnpj' => '52535933000187',
                'contact_name' => 'SILAS WANZELER',
                'phone' => '91985835029',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'METALFORTE PARA COMERCIO DE MATERIAIS DE CONSTRUCAO LTDA',
                'trade_name' => 'METALFORTE PARA',
                'cnpj' => '59875356000111',
                'contact_name' => 'CLEBESON',
                'phone' => '91988193680',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'SMART CENTER COMERCIO DE MATERIAIS DE CONSTRUCAO LTDA',
                'trade_name' => 'ESPACO SMART',
                'cnpj' => '19051774000170',
                'contact_name' => 'ANA ALMEIDA',
                'phone' => '91991540131',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'SAO PAULO DISTRIBUIDORA DE MATERIAIS DE CONSTRUCAO LTDA',
                'trade_name' => 'SAO PAULO DISTRIBUIDORA',
                'cnpj' => '13424484000148',
                'contact_name' => 'MAYCON',
                'phone' => '91985008736',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'À VISTA',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'CONSTRUMATE COM VAR DE MAT DE CONTRUCAO LTDA',
                'trade_name' => 'CONSTRULAR',
                'cnpj' => '63080870000138',
                'contact_name' => 'MARCOS SABINO',
                'phone' => '91984072576',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'BRAVO BRASIL IND. COM. E SERVICOS LTDA',
                'trade_name' => 'E TINTA',
                'cnpj' => '21602055000141',
                'contact_name' => 'MARCOS',
                'phone' => '9131200007',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'TUDO CASA COMERCIO DE MATERIAIS DE CONSTRUCAO EM GERAL LTDA ',
                'trade_name' => 'TUDO CASA',
                'cnpj' => '39334969000134',
                'contact_name' => 'CARLOS SILVA',
                'phone' => '9193311914',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'REFRIGERACAO DUFRIO COMERCIO E IMPORTACAO S.A.',
                'trade_name' => 'DUFRIO',
                'cnpj' => '01754239002678',
                'contact_name' => 'JULEMA',
                'phone' => '91991842911',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'R T CONSTRUTORA PISOS INDUSTRIAIS LTDA',
                'trade_name' => 'R T CONSTRUTORA PISOS INDUSTRIAIS',
                'cnpj' => '38082622000189',
                'contact_name' => 'VIVIANE',
                'phone' => '9392226675',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 2,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'ARCELORMITTAL BRASIL S.A',
                'trade_name' => 'ARCELORMITTAL BRASIL S.A',
                'cnpj' => '17469701014801',
                'contact_name' => 'KARINE',
                'phone' => '93999741916',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'REZENDE ENGENHARIA LTDA',
                'trade_name' => 'REZENDE ENGENHARIA',
                'cnpj' => '13636686000153',
                'contact_name' => 'PATRICIA',
                'phone' => '91991780226',
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'SIMOES ANDRADE COMERCIO VAREJISTA E ATACADISTA DE TINTAS DE ANANIDEUA LTDA',
                'trade_name' => 'SIMOES ANDRADE TINTAS ANANINDEUA - PINTA MUNDI',
                'cnpj' => '45057377000115',
                'contact_name' => 'VENILDA SOUSA',
                'phone' => '91992441318',
                'adms_daman_supplier_addresses_id' => 1,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'RDN LOCACAO E SERVICOS LTDA',
                'trade_name' => 'RDN LOCSERV',
                'cnpj' => '45447728000102',
                'contact_name' => 'LETÍCIA',
                'phone' => '93991010210',
                'adms_daman_supplier_addresses_id' => 1,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'ALTAMIRA INDUSTRIA E COMERCIO DE TELHAS E ACO LTDA',
                'trade_name' => 'ALTA TELHAS',
                'cnpj' => '49669598000103',
                'contact_name' => 'FERNANDA',
                'phone' => '93991682505',
                'adms_daman_supplier_addresses_id' => 1,
                'email' => 'exemple@exemple.com.br',
                'accepted_payments' => 'BOLETO',
                'adms_daman_suppliers_types_id' => 1,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],

            [
                'legal_name' => 'DHL COMERCIO E SERVIÇOS LTDA',
                'trade_name' => 'DHL LOCAÇÕES',
                'cnpj' => '05206151000114',
                'contact_name' => 'OLINDA/DIEGO',

                'phone' => '9183591000',
                'email' => 'exemple@exemple.com.br',
                'adms_daman_suppliers_types_id' => 2,
                'supplier_status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Percorrer o array com dados que devem ser validados antes de cadastrar
        foreach ($suppliers as $supplier) {

            // Verifica se a página com o cnpj especificado já existe
            $existingRecord = $this->query('SELECT id FROM adms_daman_suppliers WHERE cnpj=:cnpj', ['cnpj' => $supplier['cnpj']])->fetch();

            // Se a página não existir, adiciona seus dados ao array $data
            if (!$existingRecord) {
                $data[] = [
                    'legal_name' => $supplier['legal_name'],
                    'trade_name' => $supplier['trade_name'],
                    'cnpj' => $supplier['cnpj'],
                    'contact_name' => $supplier['contact_name'],
                    'phone' => $supplier['phone'],
                    'email' => $supplier['email'],
                    'accepted_payments' => $supplier['accepted_payments'],
                    'adms_daman_suppliers_types_id' => $supplier['adms_daman_suppliers_types_id'],
                    'supplier_status' => $supplier['supplier_status'],
                    'created_at' => date("Y-m-d H:i:s"),
                ];
            }
        }

        // Obtém a tabela 'adms_daman_suppliers' para inserir os registros
        $adms_daman_suppliers = $this->table('adms_daman_suppliers');

        // Insere os registros na tabela
        $adms_daman_suppliers->insert($data)->save();
    }
}
