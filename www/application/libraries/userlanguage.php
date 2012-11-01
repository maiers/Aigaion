<?php

/**
 * User Language
 *
 * negotiate the language of the user.
 * Feature: use $this->setAccepted ("_COOKIE") to evaluate $_COOKIE['User-Language'] instead of the HTTP header
 * There is also a PHP 5 only version with correct public/private tagging
 *
 * @author Manuel Strehl
 * @copyright (c) 2008 Manuel Strehl, some rights reserved
 * @license <http://www.gnu.org/licenses/gpl.txt>
 * @version 1.0-php4
 * @todo use correct (i.e., recommended) capitalization
 * @todo private getLanguagePart doesn't always return the correct result. Somehow this is due to the used regexp
 * @todo $iso639['got'] - the corresponding native name contains unicode characters in the range 0x103**, that parsers (PHP, editors) treat as invalid... replaced by "gothic"
 *
 */
class Userlanguage {

  /*************** MEMBERS ***************/
  
  /**
   * ISO 639 (1+2) language codes
   * 
   * [0] is the english name, [1] and following are native names
   * @source <http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes>
   */
  var $iso639 = array (
    // specials:
    "i" => array ("IANA-defined registrations"),
    "x" => array ("private use"),
  
    // ISO 639-1:
    "aa" => array ("Afar ", "Afaraf"),
    "ab" => array ("Abkhazian ", "Аҧсуа"),
    "ae" => array ("Avestan ", "avesta"),
    "af" => array ("Afrikaans ", "Afrikaans"),
    "ak" => array ("Akan ", "Akan"),
    "am" => array ("Amharic ", "አማርኛ"),
    "an" => array ("Aragonese ", "Aragonés"),
    "ar" => array ("Arabic ", "‫العربية"),
    "as" => array ("Assamese ", "অসমীয়া"),
    "av" => array ("Avaric ", "авар мацӀ", "магӀарул мацӀ"),
    "ay" => array ("Aymara ", "aymar aru"),
    "az" => array ("Azerbaijani ", "azərbaycan dili"),
    "ba" => array ("Bashkir ", "башҡорт теле"),
    "be" => array ("Belarusian ", "Беларуская"),
    "bg" => array ("Bulgarian ", "български език"),
    "bh" => array ("Bihari ", "भोजपुरी"),
    "bi" => array ("Bislama ", "Bislama"),
    "bm" => array ("Bambara ", "bamanankan"),
    "bn" => array ("Bengali ", "বাংলা"),
    "bo" => array ("Tibetan ", "བོད་ཡིག"),
    "br" => array ("Breton ", "brezhoneg"),
    "bs" => array ("Bosnian ", "bosanski jezik"),
    "ca" => array ("Catalan ", "Català"),
    "ce" => array ("Chechen ", "нохчийн мотт"),
    "ch" => array ("Chamorro ", "Chamoru"),
    "co" => array ("Corsican ", "corsu", "lingua corsa"),
    "cr" => array ("Cree ", "ᓀᐦᐃᔭᐍᐏᐣ"),
    "cs" => array ("Czech ", "česky", "čeština"),
    "cu" => array ("Church Slavic ", ""),
    "cv" => array ("Chuvash", "чӑваш чӗлхи"),
    "cy" => array ("Welsh ", "Cymraeg"),
    "da" => array ("Danish ", "dansk"),
    "de" => array ("German ", "Deutsch"),
    "dv" => array ("Divehi ", "‫ދިވެހި"),
    "dz" => array ("Dzongkha ", "རྫོང་ཁ"),
    "ee" => array ("Ewe ", "Ɛʋɛgbɛ"),
    "el" => array ("Greek ", "Ελληνικά"),
    "en" => array ("English ", "English"),
    "eo" => array ("Esperanto ", "Esperanto"),
    "es" => array ("Spanish ", "español", "castellano"),
    "et" => array ("Estonian ", "Eesti keel"),
    "eu" => array ("Basque ", "euskara"),
    "fa" => array ("Persian ", "‫فارسی"),
    "ff" => array ("Fulah ", "Fulfulde"),
    "fi" => array ("Finnish ", "suomen kieli"),
    "fj" => array ("Fijian ", "vosa Vakaviti"),
    "fo" => array ("Faroese ", "Føroyskt"),
    "fr" => array ("French ", "français", "langue française"),
    "fy" => array ("Western Frisian ", "Frysk"),
    "ga" => array ("Irish ", "Gaeilge"),
    "gd" => array ("Scottish Gaelic ", "Gàidhlig"),
    "gl" => array ("Galician ", "Galego"),
    "gn" => array ("Guaraní ", "Avañe'ẽ"),
    "gu" => array ("Gujarati ", "ગુજરાતી"),
    "gv" => array ("Manx ", "Ghaelg"),
    "ha" => array ("Hausa ", "‫هَوُسَ"),
    "he" => array ("Hebrew ", "‫עברית"),
    "hi" => array ("Hindi ", "हिन्दी", "हिंदी"),
    "ho" => array ("Hiri Motu ", "Hiri Motu"),
    "hr" => array ("Croatian ", "Hrvatski"),
    "ht" => array ("Haitian ", "Kreyòl ayisyen"),
    "hu" => array ("Hungarian ", "Magyar"),
    "hy" => array ("Armenian ", "Հայերեն"),
    "hz" => array ("Herero ", "Otjiherero"),
    "ia" => array ("Interlingua", "interlingua"),
    "id" => array ("Indonesian ", "Bahasa Indonesia"),
    "ie" => array ("Interlingue ", "Interlingue"),
    "ig" => array ("Igbo ", "Igbo"),
    "ii" => array ("Sichuan Yi ", "ꆇꉙ"),
    "ik" => array ("Inupiaq ", "Iñupiaq", "Iñupiatun"),
    "io" => array ("Ido ", "Ido"),
    "is" => array ("Icelandic ", "Íslenska"),
    "it" => array ("Italian ", "Italiano"),
    "iu" => array ("Inuktitut ", "ᐃᓄᒃᑎᑐᑦ"),
    "ja" => array ("Japanese ", "日本語 (にほんご／にっぽんご)"),
    "jv" => array ("Javanese ", "basa Jawa"),
    "ka" => array ("Georgian ", "ქართული"),
    "kg" => array ("Kongo ", "KiKongo"),
    "ki" => array ("Kikuyu ", "Gĩkũyũ"),
    "kj" => array ("Kwanyama ", "Kuanyama"),
    "kk" => array ("Kazakh ", "Қазақ тілі"),
    "kl" => array ("Kalaallisut ", "kalaallisut", "kalaallit oqaasii"),
    "km" => array ("Khmer ", "ភាសាខ្មែរ"),
    "kn" => array ("Kannada ", "ಕನ್ನಡ"),
    "ko" => array ("Korean ", "한국어 (韓國語)", "조선말 (朝鮮語)"),
    "kr" => array ("Kanuri ", "Kanuri"),
    "ks" => array ("Kashmiri ", "कश्मीरी", "كشميري‎"),
    "ku" => array ("Kurdish ", "Kurdî", "كوردی‎"),
    "kv" => array ("Komi ", "коми кыв"),
    "kw" => array ("Cornish ", "Kernewek"),
    "ky" => array ("Kirghiz ", "кыргыз тили"),
    "la" => array ("Latin ", "latine", "lingua latina"),
    "lb" => array ("Luxembourgish ", "Lëtzebuergesch"),
    "lg" => array ("Ganda ", "Luganda"),
    "li" => array ("Limburgish ", "Limburgs"),
    "ln" => array ("Lingala ", "Lingála"),
    "lo" => array ("Lao ", "ພາສາລາວ"),
    "lt" => array ("Lithuanian ", "lietuvių kalba"),
    "lu" => array ("Luba-Katanga ", ""),
    "lv" => array ("Latvian", "latviešu valoda"),
    "mg" => array ("Malagasy ", "Malagasy fiteny"),
    "mh" => array ("Marshallese ", "Kajin M̧ajeļ"),
    "mi" => array ("Māori ", "te reo Māori"),
    "mk" => array ("Macedonian ", "македонски јазик"),
    "ml" => array ("Malayalam ", "മലയാളം"),
    "mn" => array ("Mongolian ", "Монгол"),
    "mo" => array ("Moldavian ", "лимба молдовеняскэ"),
    "mr" => array ("Marathi ", "मराठी"),
    "ms" => array ("Malay ", "bahasa Melayu", "بهاس ملايو‎"),
    "mt" => array ("Maltese ", "Malti"),
    "my" => array ("Burmese ", "ဗမာစာ"),
    "na" => array ("Nauru ", "Ekakairũ Naoero"),
    "nb" => array ("Norwegian Bokmål ", "Norsk bokmål"),
    "nd" => array ("North Ndebele ", "isiNdebele"),
    "ne" => array ("Nepali ", "नेपाली"),
    "ng" => array ("Ndonga ", "Owambo"),
    "nl" => array ("Dutch ", "Nederlands"),
    "nn" => array ("Norwegian Nynorsk ", "Norsk nynorsk"),
    "no" => array ("Norwegian ", "Norsk"),
    "nr" => array ("South Ndebele ", "Ndébélé"),
    "nv" => array ("Navajo ", "Diné bizaad", "Dinékʼehǰí"),
    "ny" => array ("Chichewa ", "chiCheŵa", "chinyanja"),
    "oc" => array ("Occitan ", "Occitan"),
    "oj" => array ("Ojibwa ", "ᐊᓂᔑᓈᐯᒧᐎᓐ"),
    "om" => array ("Oromo ", "Afaan Oromoo"),
    "or" => array ("Oriya ", "ଓଡ଼ିଆ"),
    "os" => array ("Ossetian ", "Ирон æвзаг"),
    "pa" => array ("Panjabi ", "ਪੰਜਾਬੀ", "پنجابی‎"),
    "pi" => array ("Pāli ", "पाऴि"),
    "pl" => array ("Polish ", "polski"),
    "ps" => array ("Pashto ", "‫پښتو"),
    "pt" => array ("Portuguese ", "Português"),
    "qu" => array ("Quechua ", "Runa Simi", "Kichwa"),
    "rm" => array ("Raeto-Romance ", "rumantsch grischun"),
    "rn" => array ("Kirundi ", "kiRundi"),
    "ro" => array ("Romanian ", "română"),
    "ru" => array ("Russian ", "русский язык"),
    "rw" => array ("Kinyarwanda ", "Kinyarwanda"),
    "sa" => array ("Sanskrit ", "संस्कृतम्"),
    "sc" => array ("Sardinian ", "sardu"),
    "sd" => array ("Sindhi ", "सिन्धी", "‫سنڌي، سندھی‎"),
    "se" => array ("Northern Sami ", "Davvisámegiella"),
    "sg" => array ("Sango ", "yângâ tî sängö"),
    "sh" => array ("Serbo-Croatian ", "Srpskohrvatski/Српскохрватски"),
    "si" => array ("Sinhalese ", "සිංහල"),
    "sk" => array ("Slovak ", "slovenčina"),
    "sl" => array ("Slovenian ", "slovenščina"),
    "sm" => array ("Samoan ", "gagana fa'a Samoa"),
    "sn" => array ("Shona ", "chiShona"),
    "so" => array ("Somali ", "Soomaaliga", "af Soomaali"),
    "sq" => array ("Albanian ", "Shqip"),
    "sr" => array ("Serbian ", "српски језик"),
    "ss" => array ("Swati ", "SiSwati"),
    "st" => array ("Sotho ", "seSotho"),
    "su" => array ("Sundanese ", "Basa Sunda"),
    "sv" => array ("Swedish ", "Svenska"),
    "sw" => array ("Swahili ", "Kiswahili"),
    "ta" => array ("Tamil ", "தமிழ்"),
    "te" => array ("Telugu ", "తెలుగు"),
    "tg" => array ("Tajik ", "тоҷикӣ", "toğikī", "‫تاجیکی‎"),
    "th" => array ("Thai ", "ไทย"),
    "ti" => array ("Tigrinya ", "ትግርኛ"),
    "tk" => array ("Turkmen ", "Türkmen", "Түркмен"),
    "tl" => array ("Tagalog ", "Tagalog"),
    "tn" => array ("Tswana ", "seTswana"),
    "to" => array ("Tonga ", "faka Tonga"),
    "tr" => array ("Turkish ", "Türkçe"),
    "ts" => array ("Tsonga ", "xiTsonga"),
    "tt" => array ("Tatar ", "татарча", "tatarça", "‫تاتارچا‎"),
    "tw" => array ("Twi ", "Twi"),
    "ty" => array ("Tahitian ", "Reo Mā`ohi"),
    "ug" => array ("Uighur ", "Uyƣurqə", "‫ئۇيغۇرچ ‎"),
    "uk" => array ("Ukrainian ", "Українська"),
    "ur" => array ("Urdu ", "‫اردو"),
    "uz" => array ("Uzbek ", "O'zbek", "Ўзбек", "أۇزبېك‎"),
    "ve" => array ("Venda ", "tshiVenḓa"),
    "vi" => array ("Vietnamese ", "Tiếng Việt"),
    "vo" => array ("Volapük ", "Volapük"),
    "wa" => array ("Walloon ", "Walon"),
    "wo" => array ("Wolof ", "Wollof"),
    "xh" => array ("Xhosa ", "isiXhosa"),
    "yi" => array ("Yiddish ", "‫ייִדיש"),
    "yo" => array ("Yoruba ", "Yorùbá"),
    "za" => array ("Zhuang ", "Saɯ cueŋƅ", "Saw cuengh"),
    "zh" => array ("Chinese ", "中文, 汉语, 漢語"),
    "zu" => array ("Zulu ", "isiZulu"),
    
    // ISO 639-2:
    "abk" => array ("Abkhaz", "Аҧсуа"),
    "ace" => array ("Acehnese, Achinese", "Aceh"),
    "ach" => array ("Acoli"),
    "ada" => array ("Adangme", "adangbɛ"),
    "ady" => array ("Adyghe", "Adygei", "адыгэбзэ", "адыгабзэ"),
    "aar" => array ("Afar", "Afaraf"),
    "afh" => array ("Afrihili"),
    "afr" => array ("Afrikaans", "Afrikaans"),
    "afa" => array ("Afro-Asiatic (Other)"),
    "ain" => array ("Ainu", "アイヌ イタㇰ(イタッㇰ)"),
    "aka" => array ("Akan"),
    "akk" => array ("Akkadian", "akkadû", "lišānum akkadītum"),
    "alb" => array ("Albanian", "Shqip"),
    "sqi" => array ("Albanian", "Shqip"),
    "ale" => array ("Aleut"),
    "alg" => array ("Algonquian languages"),
    "tut" => array ("Altaic (Other)"),
    "amh" => array ("Amharic", "አማርኛ"),
    "anp" => array ("Angika"),
    "apa" => array ("Apache languages"),
    "ara" => array ("Arabic", "العربية"),
    "arg" => array ("Aragonese", "Aragonés"),
    "arc" => array ("Aramaic", "ܐܪܡܝܐ"),
    "arp" => array ("Arapaho", "Hinóno'eitíít"),
    "arn" => array ("Araucanian", "mapudungun", "mapuchedungun"),
    "arw" => array ("Arawak"),
    "arm" => array ("Armenian", "Հայերեն լեզու"),
    "hye" => array ("Armenian", "Հայերեն լեզու"),
    "rup" => array ("Aromanian", "Arumanian", "Macedo-Romanian", "Armãneashce", "Armãneashti", "Limba armãneascã"),
    "art" => array ("Artificial (Other)"),
    "asm" => array ("Assamese", "অসমীয়া"),
    "ast" => array ("Asturian", "Bable", "asturianu"),
    "ath" => array ("Athapascan languages"),
    "aus" => array ("Australian languages"),
    "bar" => array ("Austro-Bavarian", "boarisch"),
    "map" => array ("Austronesian (Other)"),
    "ava" => array ("Avaric", "авар мацӀ", "магӀарул мацӀ"),
    "ave" => array ("Avestan", "avesta"),
    "awa" => array ("Awadhi", "अवधी"),
    "aym" => array ("Aymara", "aymar aru"),
    "aze" => array ("Azerbaijani", "Azərbaycanca"),
    "ban" => array ("Balinese", "Basa Bali"),
    "bat" => array ("Baltic (Other)"),
    "bal" => array ("Baluchi", "بلوچی"),
    "bam" => array ("Bambara", "bamanankan"),
    "bai" => array ("Bamileke languages"),
    "bad" => array ("Banda"),
    "bnt" => array ("Bantu (Other)"),
    "bas" => array ("Basa", "ɓasaá"),
    "bak" => array ("Bashkir", "башҡорт теле"),
    "baq" => array ("Basque", "euskara"),
    "eus" => array ("Basque", "euskara"),
    "btk" => array ("Batak (Indonesia)"),
    "bej" => array ("Beja", "بداوية"),
    "bel" => array ("Belarusian", "Беларуская мова"),
    "bem" => array ("Bemba", "ichiBemba"),
    "ben" => array ("Bengali", "বাংলা"),
    "ber" => array ("Berber (Other)"),
    "bho" => array ("Bhojpuri", "भोजपुरी"),
    "bih" => array ("Bihari"),
    "bik" => array ("Bikol languages"),
    "bin" => array ("Bini", "Edo"),
    "bis" => array ("Bislama", "Bislama"),
    "byn" => array ("Blin", "Bilin", "ብሊና"),
    "bos" => array ("Bosnian", "bosanski jezik"),
    "bra" => array ("Brij Bhasha", "ब्रज भाषा"),
    "bre" => array ("Breton", "brezhoneg"),
    "bug" => array ("Buginese", "ᨅᨔ ᨕᨘᨁᨗ"),
    "bul" => array ("Bulgarian", "български език"),
    "bua" => array ("Buriat", "буряад хэлэн"),
    "bur" => array ("Burmese", "ျမန္မာစာ"),
    "mya" => array ("Burmese", "ျမန္မာစာ"),
    "cad" => array ("Caddo", "Hasí:nay"),
    "car" => array ("Carib"),
    "cat" => array ("Catalan", "Valencian", "català"),
    "cau" => array ("Caucasian (Other)"),
    "ceb" => array ("Cebuano", "Sinugboanon"),
    "cel" => array ("Celtic (Other)"),
    "cai" => array ("Central American Indian (Other)"),
    "chg" => array ("Chagatai", "جغتای"),
    "cmc" => array ("Chamic languages"),
    "cha" => array ("Chamorro", "Chamoru"),
    "che" => array ("Chechen", "нохчийн мотт"),
    "chr" => array ("Cherokee", "ᏣᎳᎩ"),
    "chy" => array ("Cheyenne", "Tsêhést"),
    "chb" => array ("Chibcha"),
    "nya" => array ("Chichewa", "Chewa", "Nyanja", "chiCheŵa", "chinyanja"),
    "chi" => array ("Chinese", "中文"),
    "zho" => array ("Chinese", "中文"),
    "chn" => array ("Chinook jargon"),
    "cho" => array ("Choctaw", "Chahta Anumpa"),
    "chu" => array ("Church Slavonic", "Church Slavic", "Old Church Slavonic", "Old Slavonic", "Old Bulgarian", "ѩзыкъ словѣньскъ"),
    "chk" => array ("Chuukese"),
    "chv" => array ("Chuvash", "чӑваш чӗлхи"),
    "nwc" => array ("Classical Newari", "Old Newari", "Classical Nepal Bhasa"),
    "syc" => array ("Classical Syriac"),
    "cop" => array ("Coptic", "ⲙⲉⲧⲛ̀ⲣⲉⲙⲛ̀ⲭⲏⲙⲓ"),
    "cor" => array ("Cornish", "Kernewek"),
    "cos" => array ("Corsican", "corsu", "lingua corsa"),
    "cre" => array ("Cree", "ᓀᐦᐃᔭᐍᐏᐣ"),
    "mus" => array ("Creek", "Muskogean", "Maskoki", "Mvskokē empunakv"),
    "crp" => array ("Creoles and Pidgins (Other)"),
    "cpe" => array ("Creoles and Pidgins, English-based (Other)"),
    "cpf" => array ("Creoles and Pidgins, French-based (Other)"),
    "cpp" => array ("Creoles and Pidgins, Portuguese-based (Other)"),
    "crh" => array ("Crimean Tatar (Crimean Turkish)", "qırımtatar tili, къырымтатар тили"),
    "scr" => array ("Croatian", "hrvatski jezik"),
    "hrv" => array ("Croatian", "hrvatski jezik"),
    "cus" => array ("Cushitic (Other)"),
    "cze" => array ("Czech", "česky", "čeština"),
    "ces" => array ("Czech", "česky", "čeština"),
    "dak" => array ("Dakota", "Lakhota"),
    "dan" => array ("Danish", "dansk"),
    "dar" => array ("Dargwa", "дарган мез"),
    "day" => array ("Dayak"),
    "del" => array ("Delaware", "Lënape"),
    "chp" => array ("Dene Suline", "Dëne Sųłiné", "ᑌᓀᓲᒢᕄᓀ"),
    "din" => array ("Dinka", "Thuɔŋjäŋ"),
    "div" => array ("Divehi", "Dhivehi", "Maldivian", "ދިވެހިބަސ"),
    "doi" => array ("Dogri", "डोगरी"),
    "dgr" => array ("Dogrib", "Tłįchǫ"),
    "dra" => array ("Dravidian (Other)"),
    "dua" => array ("Duala"),
    "dut" => array ("Dutch", "Flemish", "Nederlands"),
    "nld" => array ("Dutch", "Flemish", "Nederlands"),
    "dum" => array ("Dutch, Middle (ca. 1050–1350)"),
    "dyu" => array ("Dyula", "Julakan"),
    "dzo" => array ("Dzongkha", "རྫོང་ཁ"),
    "efi" => array ("Efik"),
    "egy" => array ("Egyptian (Ancient)"),
    "eka" => array ("Ekajuk"),
    "elx" => array ("Elamite"),
    "eng" => array ("English", "English"),
    "enm" => array ("English, Middle (ca. 1100–1500)"),
    "ang" => array ("English, Old (ca. 450–1100)", "Englisc"),
    "myv" => array ("Erzya", "эрзянь кель"),
    "epo" => array ("Esperanto", "Esperanto"),
    "est" => array ("Estonian", "eesti keel"),
    "ewe" => array ("Ewe", "Ɛʋɛgbɛ"),
    "ewo" => array ("Ewondo"),
    "fan" => array ("Fang"),
    "fat" => array ("Fanti"),
    "fao" => array ("Faroese", "føroyskt"),
    "fij" => array ("Fijian", "vosa Vakaviti"),
    "fil" => array ("Filipino"),
    "fin" => array ("Finnish", "suomi", "suomen kieli"),
    "fiu" => array ("Finno-Ugrian (Other)"),
    "fon" => array ("Fon", "Fɔngbe"),
    "fre" => array ("French", "français", "langue française"),
    "fra" => array ("French", "français", "langue française"),
    "frm" => array ("French, Middle (ca. 1400—1600)"),
    "fro" => array ("French, Old (842—ca. 1400)"),
    "frs" => array ("Frisian, Eastern", "Seeltersk Fräisk", "Seeltersk", "Fräisk"),
    "frr" => array ("Frisian, Northern"),
    "fry" => array ("Frisian, Western", "frysk"),
    "fur" => array ("Friulian", "furlan"),
    "ful" => array ("Fulah", "Fulfulde, Pulaar, Pular"),
    "gaa" => array ("Ga", "Gã"),
    "glg" => array ("Galician", "Galego"),
    "lug" => array ("Ganda", "Luganda"),
    "gay" => array ("Gayo"),
    "gba" => array ("Gbaya"),
    "gez" => array ("Ge'ez", "ግዕዝ"),
    "geo" => array ("Georgian", "ქართული ენა (kartuli ena)"),
    "kat" => array ("Georgian", "ქართული ენა (kartuli ena)"),
    "ger" => array ("German", "Deutsch"),
    "deu" => array ("German", "Deutsch"),
    "gsw" => array ("German, Alemannic", "Alemannisch, Schwyzerdütsch"),
    "nds" => array ("German, Low", "Low German", "Saxon, Low", "Low Saxon", "Nederdüütsch, Plattdüütsch"),
    "gmh" => array ("German, Middle High (ca. 1050–1500)", "diutisk"),
    "goh" => array ("German, Old High (ca. 750–1050)", "diutisc"),
    "gem" => array ("Germanic (Other)"),
    "gil" => array ("Gilbertese", "Kiribati", "taetae ni Kiribati"),
    "gon" => array ("Gondi", "Gōndi"),
    "gor" => array ("Gorontalo"),
    "got" => array ("Gothic", "gothic"),
    "grb" => array ("Grebo"),
    "grc" => array ("Greek, Ancient (to 1453)", "Ἑλληνικά"),
    "gre" => array ("Greek, Modern (1453–)", "Ελληνικά"),
    "ell" => array ("Greek, Modern (1453–)", "Ελληνικά"),
    "kal" => array ("Greenlandic", "Kalaallisut", "kalaallisut", "kalaallit oqaasii"),
    "grn" => array ("Guarani", "Avañe'ẽ"),
    "guj" => array ("Gujarati", "ગુજરાતી"),
    "gwi" => array ("Gwichʼin"),
    "hai" => array ("Haida", "X̲aat Kíl"),
    "hat" => array ("Haitian Creole", "Haitian", "Kreyòl ayisyen"),
    "hau" => array ("Hausa", "Hausancī", "هَوُسَ"),
    "haw" => array ("Hawaiian", "‘Ōlelo Hawai‘i"),
    "heb" => array ("Hebrew", "עִבְרִית", "עברית"),
    "her" => array ("Herero", "Otjiherero"),
    "hil" => array ("Hiligaynon", "Ilonggo"),
    "him" => array ("Himachali"),
    "hin" => array ("Hindi", "हिन्दी"),
    "hmo" => array ("Hiri Motu", "Hiri Motu"),
    "hit" => array ("Hittite"),
    "hmn" => array ("Hmong", "Hmoob"),
    "hun" => array ("Hungarian", "Magyar"),
    "hup" => array ("Hupa", "Na:tinixwe Mixine:whe"),
    "iba" => array ("Iban"),
    "ice" => array ("Icelandic", "íslenska"),
    "isl" => array ("Icelandic", "íslenska"),
    "ido" => array ("Ido", "Ido"),
    "ibo" => array ("Igbo", "Igbo"),
    "ijo" => array ("Ijo"),
    "ilo" => array ("Iloko"),
    "smn" => array ("Inari Sami", "säämegiella"),
    "inc" => array ("Indic (Other)"),
    "ine" => array ("Indo-European (Other)"),
    "ind" => array ("Indonesian", "Bahasa Indonesia"),
    "inh" => array ("Ingush", "гӀалгӀай мотт"),
    "ina" => array ("Interlingua (International Auxiliary Language Association)", "interlingua"),
    "ile" => array ("Interlingue", "Interlingue"),
    "iku" => array ("Inuktitut", "ᐃᓄᒃᑎᑐᑦ"),
    "ipk" => array ("Inupiaq", "Iñupiaq", "Iñupiatun"),
    "ira" => array ("Iranian (Other)"),
    "gle" => array ("Irish", "Gaeilge"),
    "mga" => array ("Irish, Middle (900–1200)", "Gaoidhealg"),
    "sga" => array ("Irish, Old (to 900)", "Goídelc"),
    "iro" => array ("Iroquoian languages"),
    "ita" => array ("Italian", "italiano"),
    "jpn" => array ("Japanese", "日本語"),
    "jav" => array ("Javanese", "basa Jawa"),
    "jrb" => array ("Judeo-Arabic"),
    "jpr" => array ("Judeo-Persian"),
    "kbd" => array ("Kabardian", "къэбэрдеибзэ"),
    "kab" => array ("Kabyle", "Taqbaylit"),
    "kac" => array ("Kachin", "Jingpho", "Marip"),
    "xal" => array ("Kalmyk", "Oirat", "хальмг келн"),
    "kam" => array ("Kamba"),
    "kan" => array ("Kannada", "ಕನ್ನಡ"),
    "kau" => array ("Kanuri"),
    "krc" => array ("Karachay-Balkar", "къарачай-малкъар тил"),
    "kaa" => array ("Kara-Kalpak", "қарақалпақ тили"),
    "krl" => array ("Karelian", "karjalan kieli"),
    "kar" => array ("Karen"),
    "kas" => array ("Kashmiri", "कॉशुर", "کٲشُر"),
    "csb" => array ("Kashubian", "kaszëbsczi jãzëk"),
    "kaw" => array ("Kawi", "Bhāṣa Kawi"),
    "kaz" => array ("Kazakh", "Қазақ тілі"),
    "kha" => array ("Khasi", "Khasi"),
    "khm" => array ("Khmer", "Image:PhiesaKhmae.gif"),
    "khi" => array ("Khoisan (Other)"),
    "kho" => array ("Khotanese"),
    "kik" => array ("Kikuyu", "Gĩkũyũ"),
    "kmb" => array ("Kimbundu"),
    "kin" => array ("Kinyarwanda", "kinyaRwanda"),
    "kir" => array ("Kirghiz", "кыргыз тили"),
    "tlh" => array ("Klingon", "tlhIngan Hol"),
    "kom" => array ("Komi", "коми кыв"),
    "kon" => array ("Kongo", "Kikongo"),
    "kok" => array ("Konkani", "कोंकणी"),
    "kor" => array ("Korean", "한국어"),
    "kos" => array ("Kosraean", "Kosrae"),
    "kpe" => array ("Kpelle", "kpele"),
    "kro" => array ("Kru"),
    "kua" => array ("Kuanyama", "Kwanyama"),
    "kum" => array ("Kumyk", "Кумык"),
    "kur" => array ("Kurdish", "Kurdî"),
    "kru" => array ("Kurukh"),
    "kut" => array ("Kutenai", "Ktunaxa"),
    "lad" => array ("Ladino", "ג'ודיאו-איספאנייול"),
    "lah" => array ("Lahnda", "ਲਹਿੰਦੀ"),
    "lam" => array ("Lamba"),
    "lao" => array ("Lao", "ພາສາລາວ"),
    "lat" => array ("Latin", "latine", "lingua latina"),
    "lav" => array ("Latvian", "latviešu valoda"),
    "lez" => array ("Lezghian", "лезги чӀал"),
    "lim" => array ("Limburgish", "Limburger", "Limburgan", "Limburgs"),
    "lin" => array ("Lingala", "lingala"),
    "lit" => array ("Lithuanian", "lietuvių kalba"),
    "jbo" => array ("Lojban", "lojban"),
    "loz" => array ("Lozi", "siLozi"),
    "lub" => array ("Luba-Katanga"),
    "lua" => array ("Luba-Lulua", "lwaà:"),
    "lui" => array ("Luiseño"),
    "smj" => array ("Lule Sami", "sámegiella"),
    "lun" => array ("Lunda", "chiLunda"),
    "luo" => array ("Luo (Kenya and Tanzania)", "Dholuo"),
    "lus" => array ("Lushai"),
    "ltz" => array ("Luxembourgish", "Letzeburgesch", "Lëtzebuergesch"),
    "mac" => array ("Macedonian", "македонски јазик"),
    "mkd" => array ("Macedonian", "македонски јазик"),
    "mad" => array ("Madurese"),
    "mag" => array ("Magahi"),
    "mai" => array ("Maithili", "मैथिली"),
    "mak" => array ("Makasar"),
    "mlg" => array ("Malagasy", "Malagasy fiteny"),
    "may" => array ("Malay", "bahasa Melayu", "بهاس ملايو"),
    "msa" => array ("Malay", "bahasa Melayu", "بهاس ملايو"),
    "mal" => array ("Malayalam", "മലയാളം"),
    "mlt" => array ("Maltese", "Malti"),
    "mnc" => array ("Manchu", "ᠮᠠᠨᠵᡠ ᡤᡳᠰᡠᠨ ᠪᡝ"),
    "mdr" => array ("Mandar"),
    "man" => array ("Mandingo"),
    "mni" => array ("Manipuri", "মৈইতৈইলোন"),
    "mno" => array ("Manobo languages"),
    "glv" => array ("Manx", "Gaelg", "Manninagh"),
    "mao" => array ("Māori", "te reo Māori"),
    "mri" => array ("Māori", "te reo Māori"),
    "mar" => array ("Marathi", "मराठी"),
    "chm" => array ("Mari", "марий йылме"),
    "mah" => array ("Marshallese", "Kajin M̧ajeļ"),
    "mwr" => array ("Marwari", "मारवाड़ी"),
    "mas" => array ("Masai", "ɔl Maa"),
    "myn" => array ("Mayan languages"),
    "men" => array ("Mende", "Mɛnde"),
    "mic" => array ("Mi'kmaq", "Micmac", "Míkmaq, Mi'gmaq"),
    "min" => array ("Minangkabau", "Baso Minangkabau"),
    "mwl" => array ("Mirandese", "Lhéngua Mirandesa"),
    "moh" => array ("Mohawk", "Kanien’keha"),
    "mdf" => array ("Moksha", "мокшень кяль"),
    "mol" => array ("Moldavian", "лимба молдовеняскэ"),
    "mkh" => array ("Mon-Khmer (Other)"),
    "lol" => array ("Mongo"),
    "mon" => array ("Mongolian", "монгол хэл"),
    "mos" => array ("Mossi", "Mòoré"),
    "mun" => array ("Munda languages"),
    "nah" => array ("Nahuatl", "nāhuatl", "nawatlahtolli"),
    "nau" => array ("Nauruan", "Ekakairũ Naoero"),
    "nav" => array ("Navajo", "Navaho", "Diné bizaad", "Dinékʼehǰí"),
    "nde" => array ("Ndebele, North", "isiNdebele"),
    "nbl" => array ("Ndebele, South", "Ndébélé"),
    "ndo" => array ("Ndonga", "Owambo"),
    "nap" => array ("Neapolitan", "napulitano"),
    "new" => array ("Nepal Bhasa", "Newari", "Nepal Bhasa"),
    "nep" => array ("Nepali", "नेपाली"),
    "nia" => array ("Nias"),
    "nic" => array ("Niger-Kordofanian (Other)"),
    "ssa" => array ("Nilo-Saharan (Other)"),
    "niu" => array ("Niuean", "ko e vagahau Niuē", "faka-Niue"),
    "nqo" => array ("N'Ko"),
    "nog" => array ("Nogai", "ногай тили"),
    "non" => array ("Norse, Old", "norskr"),
    "nai" => array ("North American Indian (Other)"),
    "sme" => array ("Northern Sami", "sámi", "sámegiella"),
    "nor" => array ("Norwegian", "Norsk"),
    "nob" => array ("Norwegian Bokmål", "Norsk bokmål"),
    "nno" => array ("Norwegian Nynorsk", "Norsk nynorsk"),
    "nub" => array ("Nubian languages"),
    "nym" => array ("Nyamwezi", "Kinyamwezi"),
    "nyn" => array ("Nyankole"),
    "nyo" => array ("Nyoro", "Runyoro"),
    "nzi" => array ("Nzima"),
    "oci" => array ("Occitan (post 1500)", "Provençal", "Occitan"),
    "oji" => array ("Ojibwa, Anishinaabe languages", "ᐊᓂᔑᓇᐯᒧᐏᐣ (Anishinaabemowin)"),
    "ori" => array ("Oriya", "ଓଡ଼ିଆ"),
    "orm" => array ("Oromo", "Afaan Oromoo"),
    "osa" => array ("Osage"),
    "oss" => array ("Ossetian", "Ossetic", "ирон ӕвзаг"),
    "oto" => array ("Otomian languages"),
    "pal" => array ("Pahlavi (Middle Persian)"),
    "pau" => array ("Palauan", "tekoi ra Belau"),
    "pli" => array ("Pali", "पालि"),
    "pam" => array ("Pampanga", "Kapampangan"),
    "pag" => array ("Pangasinan"),
    "pap" => array ("Papiamento", "Papiamentu"),
    "paa" => array ("Papuan (Other)"),
    "per" => array ("Persian", "فارسی"),
    "fas" => array ("Persian", "فارسی"),
    "peo" => array ("Persian, Old (ca. 600–400 BC)"),
    "phi" => array ("Philippine (Other)"),
    "phn" => array ("Phoenician"),
    "pon" => array ("Pohnpeian"),
    "pol" => array ("Polish", "polski"),
    "por" => array ("Portuguese", "português"),
    "pra" => array ("Prakrit languages"),
    "pro" => array ("Provençal, Old (to 1500)"),
    "pan" => array ("Punjabi", "Panjabi", "ਪੰਜਾਬੀ", "پنجابی"),
    "pus" => array ("Pushto", "پښت"),
    "que" => array ("Quechuan languages", "Runa Simi", "Kichwa"),
    "roh" => array ("Raeto-Romance", "rumantsch grischun"),
    "raj" => array ("Rajasthani", "राजस्थानी"),
    "rap" => array ("Rapanui", "rapanui", "pepito ote henua"),
    "rar" => array ("Rarotongan"),
    "roa" => array ("Romance (Other)"),
    "rum" => array ("Romanian", "română"),
    "ron" => array ("Romanian", "română"),
    "rom" => array ("Romany", "rromani ćhib", "Romani šib", "Romanó"),
    "run" => array ("Rundi", "kiRundi"),
    "rus" => array ("Russian", "русский язык"),
    "sal" => array ("Salishan languages"),
    "sam" => array ("Samaritan Aramaic", "ארמית, ܐܪܡܝܐ"),
    "smi" => array ("Sami languages (Other)"),
    "smo" => array ("Samoan", "gagana fa'a Samoa"),
    "sad" => array ("Sandawe"),
    "sag" => array ("Sango", "yângâ tî sängö"),
    "san" => array ("Sanskrit", "संस्कृतम्"),
    "sat" => array ("Santali", "संथाली"),
    "srd" => array ("Sardinian", "sardu"),
    "sas" => array ("Sasak"),
    "sco" => array ("Scots", "Scoats leid", "Lallans"),
    "gla" => array ("Scottish Gaelic", "Gaelic", "Gàidhlig"),
    "sel" => array ("Selkup", "шӧльӄумыт әты"),
    "sem" => array ("Semitic (Other)"),
    "scc" => array ("Serbian", "српски језик"),
    "srp" => array ("Serbian", "српски језик"),
    "srr" => array ("Serer"),
    "shn" => array ("Shan"),
    "sna" => array ("Shona", "chiShona"),
    "iii" => array ("Sichuan Yi", "ꆇꉙ"),
    "scn" => array ("Sicilian", "Sicilianu"),
    "sid" => array ("Sidamo", "Sidámo 'Afó"),
    "sgn" => array ("Sign languages"),
    "bla" => array ("Siksika", "siksiká", " ᓱᖽᐧᖿ"),
    "snd" => array ("Sindhi", "سنڌي، سندھی, सिन्धी"),
    "sin" => array ("Sinhalese", "Sinhala", "සිංහල"),
    "sit" => array ("Sino-Tibetan (Other)"),
    "sio" => array ("Siouan languages"),
    "sms" => array ("Skolt Sami", "sääʼmǩiõll"),
    "den" => array ("Slave (Athapascan)"),
    "sla" => array ("Slavic (Other)"),
    "slo" => array ("Slovak", "slovenčina"),
    "slk" => array ("Slovak", "slovenčina"),
    "slv" => array ("Slovenian", "slovenščina"),
    "sog" => array ("Sogdian"),
    "som" => array ("Somali", "Soomaaliga", "af Soomaali"),
    "son" => array ("Songhai"),
    "snk" => array ("Soninke", "Soninkanxaane"),
    "wen" => array ("Sorbian languages"),
    "dsb" => array ("Sorbian, Lower", "dolnoserbski"),
    "hsb" => array ("Sorbian, Upper", "hornjoserbsce"),
    "nso" => array ("Sotho, Northern", "Pedi", "Sepedi", "sePêdi"),
    "sot" => array ("Sotho, Southern", "seSotho"),
    "sai" => array ("South American Indian (Other)"),
    "alt" => array ("Southern Altai", "алтай тили"),
    "sma" => array ("Southern Sami", "saemien giele"),
    "spa" => array ("Spanish", "Castilian", "español, castellano"),
    "srn" => array ("Sranan Tongo"),
    "suk" => array ("Sukuma"),
    "sux" => array ("Sumerian", "eme-ĝir"),
    "sun" => array ("Sundanese", "basa Sunda"),
    "sus" => array ("Susu"),
    "swa" => array ("Swahili", "kiswahili"),
    "ssw" => array ("Swati", "siSwati"),
    "swe" => array ("Swedish", "Svenska"),
    "syr" => array ("Syriac", "ܣܘܪܝܝܐ"),
    "tgl" => array ("Tagalog", "Tagalog"),
    "tah" => array ("Tahitian", "te reo Tahiti", "te reo Māʼohi"),
    "tai" => array ("Tai (Other)"),
    "tgk" => array ("Tajik", "тоҷикӣ", "تاجیکی"),
    "tmh" => array ("Tamashek", "Tamajeq, ɫ[ǂ…"),
    "tam" => array ("Tamil", "தமிழ்"),
    "tat" => array ("Tatar", "татарча", "tatarça", "تاتارچا"),
    "tel" => array ("Telugu", "తెలుగు"),
    "ter" => array ("Tereno"),
    "tet" => array ("Tetum, Lia-Tetun", "Tetun"),
    "tha" => array ("Thai", "ภาษาไทย"),
    "tib" => array ("Tibetan", "བོད་ཡིག"),
    "bod" => array ("Tibetan", "བོད་ཡིག"),
    "tig" => array ("Tigre", "Tigré", "Khasa"),
    "tir" => array ("Tigrinya", "ትግርኛ"),
    "tem" => array ("Timne"),
    "tiv" => array ("Tiv"),
    "tli" => array ("Tlingit", "Lingít"),
    "tpi" => array ("Tok Pisin", "Tok Pisin"),
    "tkl" => array ("Tokelau", "Tokelau"),
    "tog" => array ("Tonga (Malawi)", "chiTonga"),
    "ton" => array ("Tongan", "faka-Tonga"),
    "tsi" => array ("Tsimshian"),
    "tso" => array ("Tsonga", "xiTsonga"),
    "tsn" => array ("Tswana", "seTswana"),
    "tum" => array ("Tumbuka", "chiTumbuka"),
    "tup" => array ("Tupi languages", "Nheengatu"),
    "tur" => array ("Turkish", "Türkçe"),
    "ota" => array ("Turkish, Ottoman (1500–1928)"),
    "tuk" => array ("Turkmen", "Түркмен"),
    "tvl" => array ("Tuvalu", "'gana Tuvalu"),
    "tyv" => array ("Tuvinian", "тыва дыл"),
    "twi" => array ("Twi"),
    "udm" => array ("Udmurt", "удмурт кыл"),
    "uga" => array ("Ugaritic"),
    "uig" => array ("Uighur", "Uyghur", "Uyƣurqə", "Uyğurçe", "ئۇيغۇرچ"),
    "ukr" => array ("Ukrainian", "українська мова"),
    "umb" => array ("Umbundu", "úmbúndú"),
    "urd" => array ("Urdu", "اردو"),
    "uzb" => array ("Uzbek", "O'zbek", "Ўзбек", "أۇزبېك"),
    "vai" => array ("Vai"),
    "ven" => array ("Venda", "tshiVenḓa"),
    "vie" => array ("Vietnamese", "Tiếng Việt"),
    "vol" => array ("Volapük", "Volapük"),
    "vot" => array ("Votic", "vaďďa tšeeli"),
    "wak" => array ("Wakashan languages"),
    "wal" => array ("Walamo"),
    "wln" => array ("Walloon", "walon"),
    "war" => array ("Waray", "Winaray", "Lineyte-Samarnon"),
    "was" => array ("Washo"),
    "wel" => array ("Welsh", "Cymraeg"),
    "cym" => array ("Welsh", "Cymraeg"),
    "wol" => array ("Wolof", "Wolof"),
    "xho" => array ("Xhosa", "isiXhosa"),
    "sah" => array ("Yakut", "Саха тыла"),
    "yao" => array ("Yao", "Chiyao"),
    "yap" => array ("Yapese"),
    "yid" => array ("Yiddish", "ייִדיש"),
    "yor" => array ("Yoruba", "Yorùbá"),
    "ypk" => array ("Yupik languages"),
    "znd" => array ("Zande", "Pazande"),
    "zza" => array ("Zaza", "Dimili", "Dimli", "Kirdki", "Kirmanjki", "Zazaki"),
    "zap" => array ("Zapotec"),
    "zen" => array ("Zenaga", "Tuḍḍungiyya"),
    "zha" => array ("Zhuang", "Chuang", "Saɯ cueŋƅ", "Saw cuengh"),
    "zul" => array ("Zulu", "isiZulu"),
    "zun" => array ("Zuni", "Shiwi")
  );
  
  /**
   * ISO 3166-1 alpha2 country codes
   * 
   * [0] is the english name, [1] and following native names.
   * @source <http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2>
   */
  var $iso3166 = array (
    "AA" => array ("User defined"),
    "AD" => array ("Andorra"),
    "AE" => array ("United Arab Emirates"),
    "AF" => array ("Afghanistan"),
    "AG" => array ("Antigua and Barbuda"),
    "AI" => array ("Anguilla"),
    "AL" => array ("Albania"),
    "AM" => array ("Armenia"),
    "AN" => array ("Netherlands Antilles"),
    "AO" => array ("Angola"),
    "AQ" => array ("Antarctica"),
    "AR" => array ("Argentina"),
    "AS" => array ("American Samoa"),
    "AT" => array ("Austria", "Österreich"),
    "AU" => array ("Australia", "Australia"),
    "AW" => array ("Aruba"),
    "AX" => array ("Åland Islands"),
    "AZ" => array ("Azerbaijan"),
    "BA" => array ("Bosnia and Herzegovina"),
    "BB" => array ("Barbados"),
    "BD" => array ("Bangladesh"),
    "BE" => array ("Belgium"),
    "BF" => array ("Burkina Faso"),
    "BG" => array ("Bulgaria"),
    "BH" => array ("Bahrain"),
    "BI" => array ("Burundi"),
    "BJ" => array ("Benin"),
    "BL" => array ("Saint Barthélemy"),
    "BM" => array ("Bermuda"),
    "BN" => array ("Brunei Darussalam"),
    "BO" => array ("Bolivia"),
    "BR" => array ("Brazil"),
    "BS" => array ("Bahamas"),
    "BT" => array ("Bhutan"),
    "BV" => array ("Bouvet Island"),
    "BW" => array ("Botswana"),
    "BY" => array ("Belarus"),
    "BZ" => array ("Belize"),
    "CA" => array ("Canada", "Canada"),
    "CC" => array ("Cocos (Keeling) Islands"),
    "CD" => array ("Congo, the Democratic Republic of the"),
    "CF" => array ("Central African Republic"),
    "CG" => array ("Congo"),
    "CH" => array ("Switzerland", "Schweiz", "Suisse", "Svizzera", "Svizra"),
    "CI" => array ("Côte d'Ivoire"),
    "CK" => array ("Cook Islands"),
    "CL" => array ("Chile", "Chile"),
    "CM" => array ("Cameroon"),
    "CN" => array ("China"),
    "CO" => array ("Colombia"),
    "CR" => array ("Costa Rica"),
    "CU" => array ("Cuba", "Cuba"),
    "CV" => array ("Cape Verde"),
    "CX" => array ("Christmas Island"),
    "CY" => array ("Cyprus"),
    "CZ" => array ("Czech Republic"),
    "DE" => array ("Germany", "Deutschland"),
    "DJ" => array ("Djibouti"),
    "DK" => array ("Denmark"),
    "DM" => array ("Dominica"),
    "DO" => array ("Dominican Republic"),
    "DZ" => array ("Algeria"),
    "EC" => array ("Ecuador"),
    "EE" => array ("Estonia"),
    "EG" => array ("Egypt"),
    "EH" => array ("Western Sahara"),
    "ER" => array ("Eritrea"),
    "ES" => array ("Spain", "España"),
    "ET" => array ("Ethiopia"),
    "FI" => array ("Finland"),
    "FJ" => array ("Fiji"),
    "FK" => array ("Falkland Islands (Malvinas)"),
    "FM" => array ("Micronesia, Federated States of"),
    "FO" => array ("Faroe Islands"),
    "FR" => array ("France", "France"),
    "GA" => array ("Gabon"),
    "GB" => array ("United Kingdom", "United Kingdom"),
    "GD" => array ("Grenada"),
    "GE" => array ("Georgia"),
    "GF" => array ("French Guiana"),
    "GG" => array ("Guernsey"),
    "GH" => array ("Ghana"),
    "GI" => array ("Gibraltar"),
    "GL" => array ("Greenland"),
    "GM" => array ("Gambia"),
    "GN" => array ("Guinea"),
    "GP" => array ("Guadeloupe"),
    "GQ" => array ("Equatorial Guinea"),
    "GR" => array ("Greece"),
    "GS" => array ("South Georgia and the South Sandwich Islands"),
    "GT" => array ("Guatemala"),
    "GU" => array ("Guam"),
    "GW" => array ("Guinea-Bissau"),
    "GY" => array ("Guyana"),
    "HK" => array ("Hong Kong"),
    "HM" => array ("Heard Island and McDonald Islands"),
    "HN" => array ("Honduras"),
    "HR" => array ("Croatia"),
    "HT" => array ("Haiti"),
    "HU" => array ("Hungary"),
    "ID" => array ("Indonesia"),
    "IE" => array ("Ireland", "Ireland", "Éire", "Airlann"),
    "IL" => array ("Israel"),
    "IM" => array ("Isle of Man", "Isle of Man", "Ellan Vannin"),
    "IN" => array ("India"),
    "IO" => array ("British Indian Ocean Territory"),
    "IQ" => array ("Iraq"),
    "IR" => array ("Iran, Islamic Republic of"),
    "IS" => array ("Iceland"),
    "IT" => array ("Italy", "Italia"),
    "JE" => array ("Jersey"),
    "JM" => array ("Jamaica"),
    "JO" => array ("Jordan"),
    "JP" => array ("Japan"),
    "KE" => array ("Kenya"),
    "KG" => array ("Kyrgyzstan"),
    "KH" => array ("Cambodia"),
    "KI" => array ("Kiribati"),
    "KM" => array ("Comoros"),
    "KN" => array ("Saint Kitts and Nevis"),
    "KP" => array ("Korea, Democratic People's Republic of"),
    "KR" => array ("Korea, Republic of"),
    "KW" => array ("Kuwait"),
    "KY" => array ("Cayman Islands"),
    "KZ" => array ("Kazakhstan"),
    "LA" => array ("Lao People's Democratic Republic"),
    "LB" => array ("Lebanon"),
    "LC" => array ("Saint Lucia"),
    "LI" => array ("Liechtenstein"),
    "LK" => array ("Sri Lanka"),
    "LR" => array ("Liberia"),
    "LS" => array ("Lesotho"),
    "LT" => array ("Lithuania"),
    "LU" => array ("Luxembourg"),
    "LV" => array ("Latvia"),
    "LY" => array ("Libyan Arab Jamahiriya"),
    "MA" => array ("Morocco"),
    "MC" => array ("Monaco"),
    "MD" => array ("Moldova, Republic of"),
    "ME" => array ("Montenegro"),
    "MF" => array ("Saint Martin (French part)"),
    "MG" => array ("Madagascar"),
    "MH" => array ("Marshall Islands"),
    "MK" => array ("Macedonia, the former Yugoslav Republic of"),
    "ML" => array ("Mali"),
    "MM" => array ("Myanmar"),
    "MN" => array ("Mongolia"),
    "MO" => array ("Macao"),
    "MP" => array ("Northern Mariana Islands"),
    "MQ" => array ("Martinique"),
    "MR" => array ("Mauritania"),
    "MS" => array ("Montserrat"),
    "MT" => array ("Malta", "Malta"),
    "MU" => array ("Mauritius"),
    "MV" => array ("Maldives"),
    "MW" => array ("Malawi"),
    "MX" => array ("Mexico", "México"),
    "MY" => array ("Malaysia"),
    "MZ" => array ("Mozambique"),
    "NA" => array ("Namibia"),
    "NC" => array ("New Caledonia"),
    "NE" => array ("Niger"),
    "NF" => array ("Norfolk Island"),
    "NG" => array ("Nigeria"),
    "NI" => array ("Nicaragua", "Nicaragua"),
    "NL" => array ("Netherlands"),
    "NO" => array ("Norway"),
    "NP" => array ("Nepal"),
    "NR" => array ("Nauru"),
    "NU" => array ("Niue"),
    "NZ" => array ("New Zealand"),
    "OM" => array ("Oman"),
    "PA" => array ("Panama"),
    "PE" => array ("Peru", "Perú", "Piruw"),
    "PF" => array ("French Polynesia"),
    "PG" => array ("Papua New Guinea"),
    "PH" => array ("Philippines"),
    "PK" => array ("Pakistan"),
    "PL" => array ("Poland"),
    "PM" => array ("Saint Pierre and Miquelon"),
    "PN" => array ("Pitcairn"),
    "PR" => array ("Puerto Rico"),
    "PS" => array ("Palestinian Territory, Occupied"),
    "PT" => array ("Portugal"),
    "PW" => array ("Palau"),
    "PY" => array ("Paraguay"),
    "QA" => array ("Qatar"),
    "QM" => array ("User defined"),
    "QN" => array ("User defined"),
    "QO" => array ("User defined"),
    "QP" => array ("User defined"),
    "QQ" => array ("User defined"),
    "QR" => array ("User defined"),
    "QS" => array ("User defined"),
    "QT" => array ("User defined"),
    "QU" => array ("User defined"),
    "QV" => array ("User defined"),
    "QW" => array ("User defined"),
    "QW" => array ("User defined"),
    "QY" => array ("User defined"),
    "QZ" => array ("User defined"),
    "RE" => array ("Réunion"),
    "RO" => array ("Romania"),
    "RS" => array ("Serbia"),
    "RU" => array ("Russian Federation"),
    "RW" => array ("Rwanda"),
    "SA" => array ("Saudi Arabia"),
    "SB" => array ("Solomon Islands"),
    "SC" => array ("Seychelles"),
    "SD" => array ("Sudan"),
    "SE" => array ("Sweden"),
    "SG" => array ("Singapore"),
    "SH" => array ("Saint Helena"),
    "SI" => array ("Slovenia"),
    "SJ" => array ("Svalbard and Jan Mayen"),
    "SK" => array ("Slovakia"),
    "SL" => array ("Sierra Leone"),
    "SM" => array ("San Marino"),
    "SN" => array ("Senegal"),
    "SO" => array ("Somalia"),
    "SR" => array ("Suriname"),
    "ST" => array ("Sao Tome and Principe"),
    "SV" => array ("El Salvador"),
    "SY" => array ("Syrian Arab Republic"),
    "SZ" => array ("Swaziland"),
    "TC" => array ("Turks and Caicos Islands"),
    "TD" => array ("Chad"),
    "TF" => array ("French Southern Territories"),
    "TG" => array ("Togo"),
    "TH" => array ("Thailand"),
    "TJ" => array ("Tajikistan"),
    "TK" => array ("Tokelau"),
    "TL" => array ("Timor-Leste"),
    "TM" => array ("Turkmenistan"),
    "TN" => array ("Tunisia"),
    "TO" => array ("Tonga"),
    "TR" => array ("Turkey"),
    "TT" => array ("Trinidad and Tobago"),
    "TV" => array ("Tuvalu"),
    "TW" => array ("Taiwan, Province of China"),
    "TZ" => array ("Tanzania, United Republic of"),
    "UA" => array ("Ukraine"),
    "UG" => array ("Uganda"),
    "UM" => array ("United States Minor Outlying Islands"),
    "US" => array ("United States", "United States of America"),
    "UY" => array ("Uruguay"),
    "UZ" => array ("Uzbekistan"),
    "VA" => array ("Holy See (Vatican City State)"),
    "VC" => array ("Saint Vincent and the Grenadines"),
    "VE" => array ("Venezuela"),
    "VG" => array ("Virgin Islands, British"),
    "VI" => array ("Virgin Islands, U.S."),
    "VN" => array ("Viet Nam"),
    "VU" => array ("Vanuatu"),
    "WF" => array ("Wallis and Futuna"),
    "WS" => array ("Samoa"),
    "XA" => array ("User defined"),
    "XB" => array ("User defined"),
    "XC" => array ("User defined"),
    "XD" => array ("User defined"),
    "XE" => array ("User defined"),
    "XF" => array ("User defined"),
    "XG" => array ("User defined"),
    "XH" => array ("User defined"),
    "XI" => array ("User defined"),
    "XJ" => array ("User defined"),
    "XK" => array ("User defined"),
    "XL" => array ("User defined"),
    "XM" => array ("User defined"),
    "XN" => array ("User defined"),
    "XO" => array ("User defined"),
    "XP" => array ("User defined"),
    "XQ" => array ("User defined"),
    "XR" => array ("User defined"),
    "XS" => array ("User defined"),
    "XT" => array ("User defined"),
    "XU" => array ("User defined"),
    "XV" => array ("User defined"),
    "XW" => array ("User defined"),
    "XX" => array ("User defined"),
    "XY" => array ("User defined"),
    "XZ" => array ("User defined"),
    "YE" => array ("Yemen"),
    "YT" => array ("Mayotte"),
    "ZA" => array ("South Africa"),
    "ZM" => array ("Zambia"),
    "ZW" => array ("Zimbabwe"),
    "ZZ" => array ("User defined"),
    
    // and the exceptional reservations:
    "AC" => array ("Ascension Island"),
    "CP" => array ("Clipperton Island"),
    "DG" => array ("Diego Garcia"),
    "EA" => array ("Ceuta and Melilla"),
    "EU" => array ("European Union"),
    "FX" => array ("France, Metropolitan"),
    "IC" => array ("Canary Islands"),
    "TA" => array ("Tristan da Cunha"),
    "UK" => array ("United Kingdom", "United Kingdom")
  );
  
  
  /**
   * ISO 15924 script tag codes
   *
   * The ISO tags for different script systems.
   * @source <http://en.wikipedia.org/wiki/List_of_ISO_15924_codes>
   */
  var $iso15924 = array (
    "Arab" => array ("Arabic", "160"),
    "Armi" => array ("Imperial Aramaic", "124"),
    "Armn" => array ("Armenian", "230"),
    "Avst" => array ("Avestan", "134"),
    "Bali" => array ("Balinese", "360"),
    "Batk" => array ("Batak", "365"),
    "Beng" => array ("Bengali", "325"),
    "Blis" => array ("Blissymbols", "550"),
    "Bopo" => array ("Bopomofo", "285"),
    "Brah" => array ("Brahmi", "300"),
    "Brai" => array ("Braille", "570"),
    "Bugi" => array ("Buginese", "367"),
    "Buhd" => array ("Buhid", "372"),
    "Cakm" => array ("Chakma", "349"),
    "Cans" => array ("Unified Canadian Aboriginal Syllabics", "440"),
    "Cari" => array ("Carian", "201"),
    "Cham" => array ("Cham", "358"),
    "Cher" => array ("Cherokee", "445"),
    "Cirt" => array ("Cirth", "291"),
    "Copt" => array ("Coptic", "204"),
    "Cprt" => array ("Cypriot", "403"),
    "Cyrl" => array ("Cyrillic", "220"),
    "Cyrs" => array ("Cyrillic (Old Church Slavonic variant)", "221"),
    "Deva" => array ("Devanagari (Nagari)", "315"),
    "Dsrt" => array ("Deseret (Mormon)", "250"),
    "Egyd" => array ("Egyptian demotic", "070"),
    "Egyh" => array ("Egyptian hieratic", "060"),
    "Egyp" => array ("Egyptian hieroglyphs", "050"),
    "Ethi" => array ("Ethiopic (Geʻez)", "430"),
    "Geor" => array ("Georgian (Mkhedruli)", "240"),
    "Geok" => array ("Khutsuri (Asomtavruli and Nuskhuri)", "241"),
    "Glag" => array ("Glagolitic", "225"),
    "Goth" => array ("Gothic", "206"),
    "Grek" => array ("Greek", "200"),
    "Gujr" => array ("Gujarati", "320"),
    "Guru" => array ("Gurmukhi", "310"),
    "Hang" => array ("Hangul (Hangŭl, Hangeul)", "286"),
    "Hani" => array ("Han (Hanzi, Kanji, Hanja)", "500"),
    "Hano" => array ("Hanunoo (Hanunóo)", "371"),
    "Hans" => array ("Simplified Chinese", "501"),
    "Hant" => array ("Traditional Chinese", "502"),
    "Hebr" => array ("Hebrew", "125"),
    "Hira" => array ("Hiragana", "410"),
    "Hmng" => array ("Pahawh Hmong", "450"),
    "Hrkt" => array ("Japanese (alias for Hiragana + Katakana)", "412"),
    "Hung" => array ("Old Hungarian", "176"),
    "Inds" => array ("Indus (Harappan)", "610"),
    "Ital" => array ("Old Italic (Etruscan, Oscan, etc.)", "210"),
    "Java" => array ("Javanese", "361"),
    "Jpan" => array ("Japanese (alias for Han + Hiragana + Katakana)", "413"),
    "Kali" => array ("Kayah Li", "357"),
    "Kana" => array ("Katakana", "411"),
    "Khar" => array ("Kharoshthi", "305"),
    "Khmr" => array ("Khmer", "355"),
    "Knda" => array ("Kannada", "345"),
    "Kore" => array ("Korean (alias for Hangul + Han)", "287"),
    "Kthi" => array ("Kaithi", "317"),
    "Lana" => array ("Lanna, Tai Tham", "351"),
    "Laoo" => array ("Lao", "356"),
    "Latf" => array ("Latin (Fraktur variant)", "217"),
    "Latg" => array ("Latin (Gaelic variant)", "216"),
    "Latn" => array ("Latin", "215"),
    "Lepc" => array ("Lepcha (Róng)", "335"),
    "Limb" => array ("Limbu", "336"),
    "Lina" => array ("Linear A", "400"),
    "Linb" => array ("Linear B", "401"),
    "Lyci" => array ("Lycian", "202"),
    "Lydi" => array ("Lydian", "116"),
    "Mani" => array ("Manichaean", "139"),
    "Mand" => array ("Mandaic, Mandaean", "140"),
    "Maya" => array ("Mayan hieroglyphs", "090"),
    "Mero" => array ("Meroitic", "100"),
    "Mlym" => array ("Malayalam", "347"),
    "Mong" => array ("Mongolian", "145"),
    "Moon" => array ("Moon (Moon code, Moon script, Moon type)", "218"),
    "Mtei" => array ("Meitei Mayek (Meithei, Meetei)", "337"),
    "Mymr" => array ("Myanmar (Burmese)", "350"),
    "Nkoo" => array ("N'Ko", "165"),
    "Ogam" => array ("Ogham", "212"),
    "Olck" => array ("Ol Chiki (Ol Cemet’, Ol, Santali)", "261"),
    "Orkh" => array ("Orkhon script", "175"),
    "Orya" => array ("Oriya script", "327"),
    "Osma" => array ("Osmanya", "260"),
    "Perm" => array ("Old Permic", "227"),
    "Phag" => array ("Phagspa", "331"),
    "Phli" => array ("Inscriptional Pahlavi", "131"),
    "Phlp" => array ("Psalter Pahlavi", "132"),
    "Phlv" => array ("Book Pahlavi", "133"),
    "Phnx" => array ("Phoenician", "115"),
    "Plrd" => array ("Pollard Phonetic", "282"),
    "Prti" => array ("Inscriptional Parthian", "130"),
    "Qaaa" => array ("reserved for private use", "900"),
    "Qaab" => array ("reserved for private use", "901"),
    "Qaac" => array ("reserved for private use", "902"),
    "Qaad" => array ("reserved for private use", "903"),
    "Qaae" => array ("reserved for private use", "904"),
    "Qaaf" => array ("reserved for private use", "905"),
    "Qaag" => array ("reserved for private use", "906"),
    "Qaah" => array ("reserved for private use", "907"),
    "Qaai" => array ("reserved for private use", "908"),
    "Qaaj" => array ("reserved for private use", "909"),
    "Qaak" => array ("reserved for private use", "910"),
    "Qaal" => array ("reserved for private use", "911"),
    "Qaam" => array ("reserved for private use", "912"),
    "Qaan" => array ("reserved for private use", "913"),
    "Qaao" => array ("reserved for private use", "914"),
    "Qaap" => array ("reserved for private use", "915"),
    "Qaaq" => array ("reserved for private use", "916"),
    "Qaar" => array ("reserved for private use", "917"),
    "Qaas" => array ("reserved for private use", "918"),
    "Qaat" => array ("reserved for private use", "919"),
    "Qaau" => array ("reserved for private use", "920"),
    "Qaav" => array ("reserved for private use", "921"),
    "Qaaw" => array ("reserved for private use", "922"),
    "Qaax" => array ("reserved for private use", "923"),
    "Qaay" => array ("reserved for private use", "924"),
    "Qaaz" => array ("reserved for private use", "925"),
    "Qaba" => array ("reserved for private use", "926"),
    "Qabb" => array ("reserved for private use", "927"),
    "Qabc" => array ("reserved for private use", "928"),
    "Qabd" => array ("reserved for private use", "929"),
    "Qabe" => array ("reserved for private use", "930"),
    "Qabf" => array ("reserved for private use", "931"),
    "Qabg" => array ("reserved for private use", "932"),
    "Qabh" => array ("reserved for private use", "933"),
    "Qabi" => array ("reserved for private use", "934"),
    "Qabj" => array ("reserved for private use", "935"),
    "Qabk" => array ("reserved for private use", "936"),
    "Qabl" => array ("reserved for private use", "937"),
    "Qabm" => array ("reserved for private use", "938"),
    "Qabn" => array ("reserved for private use", "939"),
    "Qabo" => array ("reserved for private use", "940"),
    "Qabp" => array ("reserved for private use", "941"),
    "Qabq" => array ("reserved for private use", "942"),
    "Qabr" => array ("reserved for private use", "943"),
    "Qabs" => array ("reserved for private use", "944"),
    "Qabt" => array ("reserved for private use", "945"),
    "Qabu" => array ("reserved for private use", "946"),
    "Qabv" => array ("reserved for private use", "947"),
    "Qabw" => array ("reserved for private use", "948"),
    "Qabx" => array ("reserved for private use", "949"),
    "Rjng" => array ("Rejang, Redjang, Kaganga, Aksara Kaganga", "363"),
    "Roro" => array ("Rongorongo", "620"),
    "Runr" => array ("Runic", "211"),
    "Samr" => array ("Samaritan", "123"),
    "Sara" => array ("Sarati", "292"),
    "Saur" => array ("Saurashtra", "344"),
    "Sgnw" => array ("Sign Writing", "095"),
    "Shaw" => array ("Shavian (Shaw)", "281"),
    "Sinh" => array ("Sinhala", "348"),
    "Sund" => array ("Sundanese", "362"),
    "Sylo" => array ("Syloti Nagri", "316"),
    "Syrc" => array ("Syriac", "135"),
    "Syre" => array ("Syriac (Estrangelo variant)", "138"),
    "Syrj" => array ("Syriac (Western variant)", "137"),
    "Syrn" => array ("Syriac (Eastern variant)", "136"),
    "Tagb" => array ("Tagbanwa", "373"),
    "Tale" => array ("Tai Le", "353"),
    "Talu" => array ("New Tai Lue", "354"),
    "Taml" => array ("Tamil", "346"),
    "Tavt" => array ("Tai Viet", "359"),
    "Telu" => array ("Telugu", "340"),
    "Teng" => array ("Tengwar", "290"),
    "Tfng" => array ("Tifinagh (Berber)", "120"),
    "Tglg" => array ("Tagalog", "370"),
    "Thaa" => array ("Thaana", "170"),
    "Thai" => array ("Thai", "352"),
    "Tibt" => array ("Tibetan", "330"),
    "Ugar" => array ("Ugaritic", "040"),
    "Vaii" => array ("Vai", "470"),
    "Visp" => array ("Visible Speech", "280"),
    "Xpeo" => array ("Old Persian", "030"),
    "Xsux" => array ("Cuneiform, Sumero-Akkadian", "020"),
    "Yiii" => array ("Yi", "460"),
    "Zmth" => array ("Mathematical notation", "995"),
    "Zsym" => array ("Symbols", "996"),
    "Zxxx" => array ("Code for unwritten languages", "997"),
    "Zyyy" => array ("Code for undetermined script", "998"),
    "Zzzz" => array ("Code for uncoded script", "999")
  );

  /**
   * supported languages. Can be set via $this->setSupported ()
   * should be set externally in most applications
   */
  var $supported = array (
    "en-UK" => 1.0,
    "de-DE" => 0.9,
    "en" => 0.8,
    "de" => 0.7
  );
  
  /**
   * user-accepted language. Can be set via $this->setAccept ()
   *
   * Defaults to the accept-language http header
   */
  var $accept_language = array ();
  
  /**
   * factor to diminish partial hits
   */
  var $min = 0.9;
  

  /*************** PUBLIC METHODS ***************/
  
  /**
   * constructor
   *
   * @param array|string $supported set the accepted languages
   * @param array|string $accept overwrite user's accept-language header
   * @param float $min quality factor for partial hits
   */
  function Userlanguage ($supported=false, $accept=false, $min = 0.9) {
    $this->setSupported ($supported);
    $this->setAccept ($accept);
    $this->min = $this->normalize ($min);
  }
  
  /**
   * set supported languages
   *
   * @param array|string $array replace the default supported languages. Accepts structured and plain arrays as well as strings
   */
  function setSupported ($array) {
    if (is_array ($array)) {
      if (is_numeric (key ($array))) {
        // plain array
        $this->supported = array ();
        foreach ($array as $lang) {
          $this->supported[$lang] = 1.0;
        }
      } else {
        // structured array
        $this->supported = $array;
      }
    } elseif (is_string ($array)) {
      // string
      $this->supported = $this->parse ($array);
    } else {
      // error
      return false;
    }
  }
  
  /**
   * set user accepted languages
   *
   * @param array|string $array replacement for the default accepted languages. Use '_COOKIE' to use a cookie based negotiation.
   */
  function setAccept ($array=false) {
    if ($array === "_COOKIE") {
      $array = isset ($_COOKIE['User-Language'])? $_COOKIE['User-Language'] : false;
    }
    if (!$array) {
      $array = isset ($_SERVER['HTTP_ACCEPT_LANGUAGE'])? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "en";
    }
    if (is_array ($array)) {
      if (is_numeric (key ($array))) {
        // plain array
        $this->accept_language = array ();
        foreach ($string as $lang) {
          $this->accept_language[$lang] = 1.0;
        }
      } else {
        // structured array
        $this->accept_language = $array;
      }
    } elseif (is_string ($array)) {
      $this->accept_language = $this->parse ($array);
    } else {
      // error
      return false;
    }
  }
  
  /**
   * get the highest rated language
   *
   * @param string $language overwrite the accept-language header
   */
  function get ($language = false) {
    return $this->getHighest ($this->getAll ($language));
  }
  
  /**
   * get all the supported languages as HTTP "Accept-Language" conforming string
   *
   * @param string $language overwrite the accept-language header
   */
  function getHTTPHeader ($language = false) {
    return $this->serialize ($this->getAll ($language));
  }
  
  /**
   * get the highest rated language (no region modifier)
   *
   * @param string $language overwrite the accept-language header
   */
  function getLanguage ($language = false) {
    return $this->getLanguagePart ($this->getHighest ($this->getAll ($language)));
  }
  
  /**
   * get the highest rated contry (no language code)
   *
   * @param string $language overwrite the accept-language header
   */
  function getCountry ($language = false) {
    return $this->getCountryPart ($this->getHighest ($this->getAll ($language)));
  }
  
  /**
   * get the name of the corresponding language identifier
   *
   * @param string $code ISO 639 code
   */
  function getLanguageName ($code=false) {
    if (!$code) {
      $code = $this->getLanguage ();
    } else {
      $code = $this->getLanguagePart ($code);
    }
    $code = strtolower ($code);
    if ($code && array_key_exists ($code, $this->iso639)) {
      return ($this->iso639[$code][1])? $this->iso639[$code][1] : $this->iso639[$code][0];
    } else {
      return "unknown";
    }
  }
  
  /**
   * get the name of the corresponding identifier
   *
   * @param string $code ISO 3166 code
   */
  function getCountryName ($code = false) {
    if (!$code) {
      $code = $this->getCountry ();
    } else {
      $code = $this->getCountryPart ($code);
    }
    $code = strtoupper ($code);
    if ($code && array_key_exists ($code, $this->iso3166)) {
      return ($this->iso3166[$code][1])? $this->iso3166[$code][1] : $this->iso3166[$code][0];
    } else {
      return "unknown";
    }
  }
  
  /**
   * get negotiated languages as simple sorted array
   *
   * @param string $language overwrite the accept-language header
   */
  function getArray ($language = false) {
    $langs = $this->getAll ($language);
    asort ($langs, SORT_NUMERIC);
    $return = array ();
    foreach ($langs as $l => $q) {
      $return[] = $l;
    }
    return $return;
  }
  
  /**
   * validate a language string
   *
   * @param string $language RFC4042 string to parse
   */
  function validate ($language) {
    return $this->parseLanguage ($language);
  }
  
  /**
   * magic method toString: echo the best match
   */
  function __toString () {
    return $this->get ();
  }
  
  /*************** PRIVATE METHODS ***************/
  
  /**
   * get negotiated languages as structured array
   *
   * @param string $language overwrite the accept-language header
   */
  function getAll ($language = false) {
    if (!$language) {
      $language = $this->accept_language;
    } else {
      $language = $this->parse ($language);
    }
    if (!$language || $language == "default") {
      return $this->supported;
    } else {
      return $this->getAllSupported ($language);
    }
  }
  
  /**
   * get highest rated language from a structured array
   *
   * @param array $array array with negotiated information
   * @param bool $last get the last (default: first) language with highest q
   */
  function getHighest ($array = false, $last = false) {
    if (!$array) {
      $array = $this->supported;
    }
    $lang = "";
    $q = 0.0;
    foreach ($array as $key => $value) {
      if (!$last && $value > $q) {
        $lang = $key;
        $q = $value;
      } elseif ($last && $value >= $q) {
        $lang = $key;
        $q = $value;
      }
    }
    return $lang;
  }
  
  /**
   * get all supported languages
   *
   * @param array $lang_array structured array of user languages
   */
  function getAllSupported ($lang_array) {
    $min = $this->min;
    $lang = array ();
    foreach ($lang_array as $lu => $qu) {
      foreach ($this->supported as $ls => $qs) {
        if (strtolower ($lu) == strtolower ($ls)) {
          // exact hit
          $lang[$lu] = $qu*$qs;
        } elseif (strtolower ($lu) == strtolower ($this->getLanguagePart ($ls))) {
          if (!array_key_exists ($lu, $lang) || $lang[$lu] < $qu*$qs*$min) {
            // partial hit of supported language
            $lang[$lu] = $qu*$qs*$min;
          }
        } elseif (strtolower ($ls) == strtolower ($this->getLanguagePart ($lu))) {
          if (!array_key_exists ($ls, $lang) || $lang[$ls] < $qu*$qs*$min) {
            // partial hit of user language
            $lang[$ls] = $qu*$qs*$min;
          }
        }
      }
    }
    // fallback: if there is no match, return the system's prefered language
    if (count ($lang) == 0) {
      $lang = array ($this->getHighest () => 1.0);
    }
    return $lang;
  }
  
  /**
   * get the language part of an identifier
   *
   * @param string $code the identifier to search
   * @param bool $only_ISO639_1 return only ISO639-1 alpha2 codes
   */
  function getLanguagePart ($code, $only_ISO639_1=false) {
    $code = strtolower ($code);
    $components = $this->parseLanguage ($code);
    //  the following will return wrong results for incorrect input: en-klingon
    if ($only_ISO639_1) {
      return ($components[1])? substring ($components[1], 0, 2) : false;
    } else {
      return ($components[1])? $components[1] : false;
    }
  }
  
  /**
   * get the country part of an identifier
   *
   * @param string $code the identifier to search
   */
  function getCountryPart ($code) {
    $code = strtoupper ($code);
    $components = $this->parseLanguage ($code);
    return ($components[3])? $components[3] : false;
  }
  
  /**
   * parse an accept-languages header into a structured array
   *
   * @param string $string string of format "Accept-Language"
   */
  function parse ($string) {
    // remove unwanted whitespace
    $string = preg_replace ("/[ \t\r\n\x{A0}]/u", "", $string);
    // convert "_" to "-"
    $string = str_replace ("_", "-", $string);
    $tmp = (strpos ($string, ",") > -1) ? explode (",", $string) : array ($string);
    $lang_array = array ();
    foreach ($tmp as $v) {
      if (strpos ($v, ";") > -1) {
        list ($l, $q) = explode (";", $v);
        $q = substr ($q, 2);
        $this->normalize ($q);
      } else {
        $l = $v;
        $q = 1.0;
      }
      $lang_array[$l] = $q;
    }
    return $lang_array;
  }
  
  /**
   * parse a language tag into its components
   *
   * @param string $string string of RFC 4646 format
   */
  function parseLanguage ($string) {
    // remove unwanted whitespace
    $string = preg_replace ("/[ \t\r\n\x{A0}]/u", "", $string);
    // convert "_" to "-"
    $string = str_replace ("_", "-", $string);
    $isTag = preg_match ("/^(?#
                           +- language [iso 639]: )([a-z]{2,3}(?:-[a-z]{3}){0,3}|[a-z]{4,8})(?#
                           |  script [iso 15924]: )(?:-([a-z]{4}))?(?#
                          _|  region [iso 3166]:  )(?:-([a-z]{2}|[0-9]{3}))?(?#
                           |  variant:            )(?:-((?:[0-9][a-z0-9]{3}|[a-z0-9]{5,8})(?:-(?:[0-9][a-z0-9]{3}|[a-z0-9]{5,8}))*))?(?#
                           |  extensions:         )(?:-((?:[a-wy-z](?:-[a-z0-9]{2,8})+)(?:-(?:[a-wy-z](?:-[a-z0-9]{2,8})+))*))?(?#
                           +- private use:        )(?:-(x(?:-[a-zA-Z0-9]{1,8})+))?(?#
                          --- grandfathered tags: )|(art-lojban|cel-gaulish|en-(?:boont|GB-oed|scouse)|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|tao|tay|tsu)|no-(?:bok|nyn)|sgn-(?:BE-fr|BE-nl|CH-de)|zh-(?:cmn|zh-cmn-Hans|cmn-Hant|gan|guoyu|hakka|min|min-nan|wuu|xiang|yue))(?#
                          --- RFC 3066 tags:      )|(x(?:-[a-z0-9]{1,8})+)(?#
                         )$/i",
            $string, $components);
    if ($isTag == 1) {
      return $components;
    } else {
      return false;
    }
  }
  
  /**
   * serialize a structured array into an HTTP Accept-Language conforming string
   *
   * @param array $array structured array
   */
  function serialize ($array) {
    $string = "";
    $i = 0;
    foreach ($array as $l => $q) {
      $string .= "$l";
      $this->normalize ($q);
      if ($q < 1.0) {
        $string .= ";q=$q";
      }
      if (++$i < count ($array)) {
        $string .= ",";
      }
    }
    return $string;
  }
  
  /**
   * normalize a q-value
   *
   * @param float $q q value
   * @return float the normalized q value (0 <= q <= 1.0)
   */
  function normalize (&$q, $round=false) {
    if (!is_numeric ($q) || $q < 0.0) {
      $q = 0.0;
    } elseif ($q > 1.0) {
      $q = 1.0;
    } elseif ($round) {
      $q = round ($q, 2);
    }
    return $q;
  }
  
}

/**
 * simulate the function http_negotiate_language, if it doesn't exist
 *
 * @param string|array $supported list of supported languages
 * @param array &$result passed by reference and filled with the ordered language preferences
 * @return string language code, that matches best the user preferences
 */
if (!function_exists ("http_negotiate_language")) {
  function http_negotiate_language ( $supported, &$result) {
    $handler = new UserLanguage ();
    $handler->setSupported ($supported);
    if ($result) {
      $result = $handler->getArray ();
    }
    return $handler->get ();
  }
}


?>