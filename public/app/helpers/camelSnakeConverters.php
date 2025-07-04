<?php
declare(strict_types=1);

function snakeToCamel(string $snake_term): string {
    return lcfirst(
        str_replace(' ', '',
            ucwords(str_replace('_', ' ', $snake_term))
        )
    );
}

function camelToSnake(string $camelTerm): string {
    return strtolower(
        preg_replace('/([a-z])([A-Z0-9])/', '$1_$2', $camelTerm)
    );
}

/**
 * For creating select pairs like "common_usd as commonUsd"
 */
function createStringColumnAsName(array $columns): string {
    return implode(",\n", array_map(function($column) {
        $name = snakeToCamel($column);
        return $name === $column ? $name : "$column as $name";
    }, $columns));
}

// echo camelToSnake('stefkoCredit4');
// echo snakeToCamel('stefko_credit_1');

// $columns0 = "date,
//     common_cash as commonCash,
//     common_usd as commonUsd,
//     common_usd_exchanges as commonUsdExchanges,
//     data_usd_rate as dataUsdRate,
//     data_eur_rate as dataEurRate,
//     stefko_deposit_income as stefkoDepositIncome,
//     common_income_cancel as commonIncomeCancel,
//     stefko_credit_1 as stefkoCredit1,
//     stefko_credit_2 as stefkoCredit2,
//     stefko_credit_3 as stefkoCredit3,
//     stefko_credit_4 as stefkoCredit4,
//     stefko_debit_1 as stefkoDebit1,
//     stefko_debit_2 as stefkoDebit2,
//     stefko_debit_3 as stefkoDebit3,
//     stefko_debit_4 as stefkoDebit4,
//     stefko_debit_5 as stefkoDebit5,
//     stefko_eur as stefkoEur,
//     stefko_eur_exchanges as stefkoEurExchanges,
//     stefko_income as stefkoIncome,
//     vira_black as viraBlack,
//     vira_black_income as viraBlackIncome,
//     vira_white as viraWhite,
//     vira_white_income as viraWhiteIncome,
//     vira_cash_income as viraCashIncome,
//     vira_cash_expense as viraCashExpense
// "; 

// $columnsList = ['date', 'common_cash', 'common_usd', 'common_usd_exchanges', 'data_usd_rate', 'data_eur_rate', 'stefko_deposit_income', 'common_income_cancel', 'stefko_credit_1', 'stefko_credit_2', 'stefko_credit_3', 'stefko_credit_4', 'stefko_debit_1', 'stefko_debit_2', 'stefko_debit_3', 'stefko_debit_4', 'stefko_debit_5', 'stefko_eur', 'stefko_eur_exchanges', 'stefko_income', 'vira_black', 'vira_black_income', 'vira_white', 'vira_white_income', 'vira_cash_income', 'vira_cash_expense']; 

// $re = createStringColumnAsName($columnsList);
// // echo $re, PHP_EOL;

// // print_r(explode(",\n", $re));
// $inputArray = explode(",\n", $columns0);
// $reArray = explode(",\n", $re);

// $end = count($reArray);
// for ($i = 0; $i < $end; $i++) {
//     $input = trim($inputArray[$i]);
//     $re = trim($reArray[$i]);
//     if ($input !== $re) echo $input, ' -> ', $re, PHP_EOL;
// }