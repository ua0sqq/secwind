<?php

function censure($s, $delta = 3, $continue = "\xe2\x80\xa6")
{

    static $pretext = array(
                '[уyоo]_?        (?=[еёeхx])',                '[вvbсc]_?       (?=[хпбмгжxpmgj])',          '[вvbсc]_?[ъь]_? (?=[еёe])',                  'ё_?             (?=[бb])',                           '[вvb]_?[ыi]_?',              '[зz3]_?[аa]_?',              '[нnh]_?[аaеeиi]_?',          '[вvb]_?[сc]_?          (?=[хпбмгжxpmgj])',          '[оo]_?[тtбb]_?         (?=[хпбмгжxpmgj])',          '[оo]_?[тtбb]_?[ъь]_?   (?=[еёe])',                  '[иiвvb]_?[зz3]_?       (?=[хпбмгжxpmgj])',          '[иiвvb]_?[зz3]_?[ъь]_? (?=[еёe])',                  '[иi]_?[сc]_?           (?=[хпбмгжxpmgj])',          '[пpдdg]_?[оo]_? (?> [бb]_?         (?=[хпбмгжxpmgj])
                           | [бb]_?  [ъь]_? (?=[еёe])
                           | [зz3]_? [аa] _?
                         )?',                  '[пp]_?[рr]_?[оoиi]_?',          '[зz3]_?[лl]_?[оo]_?',           '[нnh]_?[аa]_?[дdg]_?         (?=[хпбмгжxpmgj])',          '[нnh]_?[аa]_?[дdg]_?[ъь]_?   (?=[еёe])',                  '[пp]_?[оo]_?[дdg]_?          (?=[хпбмгжxpmgj])',          '[пp]_?[оo]_?[дdg]_?[ъь]_?    (?=[еёe])',                  '[рr]_?[аa]_?[зz3сc]_?        (?=[хпбмгжxpmgj])',          '[рr]_?[аa]_?[зz3сc]_?[ъь]_?  (?=[еёe])',                  '[вvb]_?[оo]_?[зz3сc]_?       (?=[хпбмгжxpmgj])',          '[вvb]_?[оo]_?[зz3сc]_?[ъь]_? (?=[еёe])',                          '[нnh]_?[еe]_?[дdg]_?[оo]_?',            '[пp]_?[еe]_?[рr]_?[еe]_?',              '[oо]_?[дdg]_?[нnh]_?[оo]_?',            '[кk]_?[oо]_?[нnh]_?[оo]_?',             '[мm]_?[уy]_?[дdg]_?[оoаa]_?',           '[oо]_?[сc]_?[тt]_?[оo]_?',              '[дdg]_?[уy]_?[рpr]_?[оoаa]_?',          '[хx]_?[уy]_?[дdg]_?[оoаa]_?',                   '[мm]_?[нnh]_?[оo]_?[гg]_?[оo]_?',            '[мm]_?[оo]_?[рpr]_?[дdg]_?[оoаa]_?',         '[мm]_?[оo]_?[зz3]_?[гg]_?[оoаa]_?',          '[дdg]_?[оo]_?[лl]_?[бb6]_?[оoаa]_?',     );

    static $badwords = array(
                '(?<=[_\d]) {RE_PRETEXT}?
         [hхx]_?[уyu]_?[йiеeёяюju]     #хуй, хуя, хую, хуем, хуёвый
         #исключения:
         (?<! _hue(?=_)    #HUE    -- цветовая палитра
            | _hue(?=so_)  #hueso  -- испанское слово
            | _хуе(?=дин)  #Хуедин -- город в Румынии
         )',

                '(?<=[_\d]) {RE_PRETEXT}?
         [пp]_?[иi]_?[зz3]_?[дd]_?[:vowel:]',  
                '(?<=[_\d]) {RE_PRETEXT}?
         [eеё]_? (?<!не[её]_) [бb6]_?(?: [уyиi]_                       #ебу, еби
                                       | [ыиiоoaаеeёуy]_?[:consonant:] #ебут, ебать, ебись, ебёт, поеботина, выебываться, ёбарь
                                       | [лl][оoаaыиi]                 #ебло, ебла, ебливая, еблись, еблысь
                                       | [нn]_?[уy]                    #ёбнул, ёбнутый
                                       | [кk]_?[аa]                    #взъёбка
                                      )',
        '(?<=[_\d]) {RE_PRETEXT}
         (?<=[^_\d][^_\d]|[^_\d]_[^_\d]_) [eеё]_?[бb6] (?:_|_?[аa]_?[^_\d])',  
                '(?<=[_\d]) {RE_PRETEXT}?
         [бb6]_?[лl]_?(?:я|ya)(?: _       #бля
                                | _?[тд]  #блять, бляди
                              )',

                '(?<=[_\d]) [пp]_?[иieе]_?[дdg]_?[eеaаoо]_?[rpр]',  
                '(?<=[_\d]) [мm]_?[уy]_?[дdg]_?[аa]',  
                '(?<=[_\d]) [zж]_?h?_?[оo]_?[pп]_?[aаyуыiеeoо]',  
                '(?<=[_\d]) [гg]_?[оo]_?[вvb]_?[нnh]_?[оoаaяеeyу]', 
                '(?<=[_\d]) f_?u_?[cс]_?k',  
    );

    static $re_trans = array(
        '_'             => '\x20',                                       '[:vowel:]'     => '[аеиоуыэюяёaeioyu]',                         '[:consonant:]' => '[^аеиоуыэюяёaeioyu\x20\d]',              );
    $re_badwords = str_replace('{RE_PRETEXT}', 
                               '(?>' . implode('|', $pretext) . ')',
                               '~' . implode('|', $badwords) . '~sxu');
    $re_badwords = strtr($re_badwords, $re_trans);

        
            
        
    static $trans = array(
        "\xc2\xad" => '',           "\xcc\x81" => '',           '/\\'      => 'л',          '/|'       => 'л',          "\xd0\xb5\xd0\xb5" => "\xd0\xb5\xd1\x91",      );
    $s = strtr($s, $trans);

            preg_match_all('/(?> \xd0[\xb0-\xbf]|\xd1[\x80-\x8f\x91]  #[а-я]
                      |  [a-z\d]+
                      )+
                    /sx', $s, $m);
    $s = ' ' . implode(' ', $m[0]) . ' ';

            $s = preg_replace('/(  [\xd0\xd1][\x80-\xbf]  #оптимизированное [а-я]
                         | [a-z\d]
                         ) \\1+
                       /sx', '$1', $s);
        if (preg_match($re_badwords, $s, $m, PREG_OFFSET_CAPTURE))
    {
        list($word, $offset) = $m[0];
        $s1 = substr($s, 0, $offset);
        $s2 = substr($s, $offset + strlen($word));
        $delta = intval($delta);
        if ($delta < 1 || $delta > 10) $delta = 3;
        preg_match('/  (?> \x20 (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)+ ){1,' . $delta . '}
                       \x20?
                    $/sx', $s1, $m1);
        preg_match('/^ (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)*  #окончание
                       \x20?
                       (?> (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)+ \x20 ){1,' . $delta . '}
                    /sx', $s2, $m2);
        $fragment = (ltrim(@$m1[0]) !== ltrim($s1) ? $continue : '') .
                    trim(@$m1[0] . '[' . trim($word) . ']' . @$m2[0]) . 
                    (rtrim(@$m2[0]) !== rtrim($s2) ? $continue : '');
        return $fragment;
    }
    return false;
}

?>