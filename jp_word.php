<!-- php -S 127.0.0.1:8000 -->
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <!-- <link href="//g.mwsrv.com/admin/SM/vegas/lib/materialdesignicons.min.css" rel="stylesheet"> -->
    <!-- <link href="//g.mwsrv.com/admin/SM/vegas/lib/vuetify.min.css" rel="stylesheet"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <title>日文練習</title>
</head>
<div id="app">
    <v-app>
        <v-main>
            <v-toolbar class=" fixed-top">
                <h1>日文練習</h1>
                <v-spacer></v-spacer>
                <v-btn icon><v-icon>mdi-chevron-down</v-icon></v-btn>
            </v-toolbar>
            <!-- table 置中用 -->
            <v-container>
                <v-tabs v-model="tab">
                    <v-tabs-slider color="#73648A"></v-tabs-slider>
                    <v-tab>未記憶出題 {{tab}} </v-tab>
                    <v-tab>全部出題 {{tab}} </v-tab>
                    <v-tab>未記憶名單</v-tab>
                    <v-tab>記憶名單</v-tab>
                    <v-tab>設定</v-tab>
                </v-tabs>

                <v-tabs-items v-model="tab" class="mb-6">
                <!-- 測驗 -->
                <v-tab-item>
                    <v-data-table
                        v-if="test_words.length"
                        :headers="test_headers"
                        :items="test_words"
                    >
                        <template v-slot:item.input="{ item, index }">
                            <v-textarea class="my-3" @compositionstart="input_compositionstart()" @compositionend="input_compositionend(0, index)" @input="updateForgetWrodValue(index)" hide-details rows="3" outlined v-model="inputAns[index]" label="日文拼音, 可輸入英文"></v-textarea>
                        </template>
                        <template v-slot:item.actions="{ item, index }">
                            <v-btn text @click="getAns(index)">不知道 看答案</v-btn>
                        </template>
                    </v-data-table>
                </v-tab-item>

                <v-tab-item>
                    <v-data-table
                        v-if="all_test_words.length"
                        :headers="test_headers"
                        :items="all_test_words"
                    >
                        <template v-slot:item.input="{ item, index }">
                            <v-textarea class="my-3" @compositionstart="input_compositionstart()" @compositionend="input_compositionend(1, index)" @input="updateValue(index)" hide-details rows="3" outlined v-model="inputAns[index]" label="日文拼音, 可輸入英文"></v-textarea>
                        </template>
                        <template v-slot:item.actions="{ item, index }">
                            <v-btn text @click="getAns(index)">不知道 看答案</v-btn>
                        </template>
                    </v-data-table>
                </v-tab-item>

                <!-- 名單 -->
                <v-tab-item>
                    <v-data-table
                        v-if="forget_words.length"
                        :headers="headers"
                        :items="forget_words"
                        :search="search"
                        :footer-props="{ 'items-per-page-options': [5, 10, 50, 100, -1], }"
                        :items-per-page="100"
                        :custom-filter="filterSearch"
                    >
                        <template v-slot:item.actions="{ item, index }">
                            <v-btn text @click="removeForget(item, index)">移出名單</v-btn>
                        </template>

                        <template v-slot:top>
                            <v-text-field v-model="search" label="搜尋比對" class="ml-4 pt-8"></v-text-field>
                        </template>
                    </v-data-table>
                </v-tab-item>
                <!-- 名單 -->
                <v-tab-item>
                    <v-data-table
                        v-if="remember_words.length"
                        :headers="headers"
                        :items="remember_words"
                        :search="search"
                        :footer-props="{ 'items-per-page-options': [5, 10, 50, 100, -1], }"
                        :items-per-page="100"
                        :custom-filter="filterSearch"
                    >
                        <template v-slot:item.actions="{ item, index }">
                            <!-- <v-icon small @click="delete_blocklist(item.uid, item.smid, item.release)">mdi-delete</v-icon> -->
                            <v-btn text @click="removeRemember(item, index)">移出名單</v-btn>
                        </template>

                        <template v-slot:top>
                            <v-text-field v-model="search" label="搜尋比對" class="ml-4 pt-8"></v-text-field>
                        </template>
                    </v-data-table>
                </v-tab-item>

                <!-- 設定頁 -->
                <v-tab-item>
                    <v-row justify="center" align="center">
                        <v-col cols="8"><v-textarea class="my-3" hide-details rows="3" outlined v-model="setting_json" label="請貼上或複製 json 設定"></v-textarea></v-col>
                        <v-col cols="2" class="text-center"><v-btn text @click="settingExport()">匯出</v-btn></v-col>
                        <v-col cols="2" class="text-center"><v-btn text @click="settingImport()">匯入</v-btn></v-col>
                    </v-row>
                </v-tab-item>
                </v-tabs-items>
            </v-container>
        </v-main>
    </v-app>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script>


var vm = new Vue({
    el: '#app',
    vuetify: new Vuetify({theme: { dark: true },}),
    data: {
        tab: 0
        , search: ''
        , inputAns: []
        , questionWord: { id: 0, word: 'word', spell: 'spell', name: 'name' }
        , setting_json: ''
        , words: [
            { "id": 0, "word": "私", "spell_jp": "わたし", "spell_en": "watashi", "name": "我" }
            , { "id": 1, "word": "彼/彼女", "spell_jp": "かれ/かのじょ", "spell_en": "kare/kanojo", "name": "他/她" }
            ,{ "id": 2, "word": "家族", "spell_jp": "かぞく", "spell_en": "kazoku", "name": "家庭" }
            ,{ "id": 3, "word": "両親", "spell_jp": "りょうしん", "spell_en": "ryoushin", "name": "父母" }
            ,{ "id": 4, "word": "祖父", "spell_jp": "そふ", "spell_en": "sofu", "name": "祖父" }
            ,{ "id": 5, "word": "祖母", "spell_jp": "そぼ", "spell_en": "sobo", "name": "祖母" }
            ,{ "id": 6, "word": "父", "spell_jp": "ちち", "spell_en": "chichi", "name": "父親" }
            ,{ "id": 7, "word": "母", "spell_jp": "はは", "spell_en": "haha", "name": "母親" }
            ,{ "id": 8, "word": "兄弟", "spell_jp": "きょうだい", "spell_en": "kyoudai", "name": "兄弟姐妹" }
            ,{ "id": 9, "word": "兄", "spell_jp": "あに", "spell_en": "ani", "name": "兄" }
            ,{ "id": 10, "word": "姉", "spell_jp": "あね", "spell_en": "ane", "name": "姐" }
            ,{ "id": 11, "word": "弟", "spell_jp": "おとうと", "spell_en": "otouto", "name": "弟" }
            ,{ "id": 12, "word": "妹", "spell_jp": "いもうと", "spell_en": "imouto", "name": "妹" }
            ,{ "id": 13, "word": "先生", "spell_jp": "せんせい", "spell_en": "sensei", "name": "老師" }
            ,{ "id": 14, "word": "学生", "spell_jp": "がくせい", "spell_en": "gakusei", "name": "學生" }
            ,{ "id": 15, "word": "友達", "spell_jp": "ともだち", "spell_en": "tomodachi", "name": "朋友" }
            ,{ "id": 16, "word": "親友", "spell_jp": "しんゆう", "spell_en": "shin'yuu", "name": "摯友" }
            ,{ "id": 17, "word": "警察", "spell_jp": "けいさつ", "spell_en": "keisatsu", "name": "警察" }
            ,{ "id": 18, "word": "会社員", "spell_jp": "かいしゃいん", "spell_en": "kaishain", "name": "公司員工" }
            ,{ "id": 19, "word": "店", "spell_jp": "みせ", "spell_en": "mise", "name": "商店" }
            ,{ "id": 20, "word": "駅", "spell_jp": "えき", "spell_en": "eki", "name": "車站" }
            ,{ "id": 21, "word": "道", "spell_jp": "みち", "spell_en": "michi", "name": "道路" }
            ,{ "id": 22, "word": "家", "spell_jp": "いえ", "spell_en": "ie", "name": "家" }
            ,{ "id": 23, "word": "部屋", "spell_jp": "へや", "spell_en": "heya", "name": "房間" }
            ,{ "id": 24, "word": "建物", "spell_jp": "たてもの", "spell_en": "tatemono", "name": "建築物" }
            ,{ "id": 25, "word": "レストラン", "spell_jp": "れすとらん", "spell_en": "resutoran", "name": "餐廳" }
            ,{ "id": 26, "word": "会社", "spell_jp": "かいしゃ", "spell_en": "kaisha", "name": "公司" }
            ,{ "id": 27, "word": "銀行", "spell_jp": "ぎんこう", "spell_en": "ginkou", "name": "銀行" }
            ,{ "id": 28, "word": "公園", "spell_jp": "こうえん", "spell_en": "kouen", "name": "公園" }
            ,{ "id": 29, "word": "病院", "spell_jp": "びょういん", "spell_en": "byouin", "name": "醫院" }
            ,{ "id": 30, "word": "学校", "spell_jp": "がっこう", "spell_en": "gakkou", "name": "學校" }
            ,{ "id": 31, "word": "教室", "spell_jp": "きょうしつ", "spell_en": "kyoushitsu", "name": "教室" }
            ,{ "id": 32, "word": "地下鉄", "spell_jp": "ちかてつ", "spell_en": "chikatetsu", "name": "地鐵" }
            ,{ "id": 33, "word": "バス停", "spell_jp": "ばすてい", "spell_en": "basutei", "name": "公車站" }
            ,{ "id": 34, "word": "喫茶店", "spell_jp": "きっさてん", "spell_en": "kissaten", "name": "咖啡店" }
            ,{ "id": 35, "word": "郵便局", "spell_jp": "ゆうびんきょく", "spell_en": "yuubinkyoku", "name": "郵局" }
            ,{ "id": 36, "word": "図書館", "spell_jp": "としょかん", "spell_en": "toshokan", "name": "圖書館" }
            ,{ "id": 37, "word": "映画館", "spell_jp": "えいがかん", "spell_en": "eigakan", "name": "電影院" }
            ,{ "id": 38, "word": "交差点", "spell_jp": "こうさてん", "spell_en": "kousaten", "name": "交叉口" }
            ,{ "id": 39, "word": "入り口", "spell_jp": "いりぐち", "spell_en": "iriguchi", "name": "入口" }
            ,{ "id": 40, "word": "出口", "spell_jp": "でぐち", "spell_en": "deguchi", "name": "出口" }
            ,{ "id": 41, "word": "アパート", "spell_jp": "あぱーと", "spell_en": "apaato", "name": "公寓" }
            ,{ "id": 42, "word": "デパート", "spell_jp": "でばーと", "spell_en": "depāto", "name": "百貨公司" }
            ,{ "id": 43, "word": "エレベーター", "spell_jp": "えれべーたー", "spell_en": "erebētā", "name": "電梯" }
            ,{ "id": 44, "word": "ホテル", "spell_jp": "ほてる", "spell_en": "hoteru", "name": "飯店" }
            ,{ "id": 45, "word": "トイレ", "spell_jp": "といれ", "spell_en": "toire", "name": "廁所" }
            ,{ "id": 46, "word": "場所", "spell_jp": "ばしょ", "spell_en": "basho", "name": "地點" }
            ,{ "id": 47, "word": "所", "spell_jp": "ところ", "spell_en": "tokoro", "name": "地方" }
            ,{ "id": 48, "word": "食べ物", "spell_jp": "たべもの", "spell_en": "tabemono", "name": "食物" }
            ,{ "id": 49, "word": "ご飯", "spell_jp": "ごはん", "spell_en": "gohan", "name": "米飯" }
            ,{ "id": 50, "word": "果物", "spell_jp": "くだもの", "spell_en": "kudamono", "name": "水果" }
            ,{ "id": 51, "word": "お菓子", "spell_jp": "おかし", "spell_en": "okashi", "name": "糖果" }
            ,{ "id": 52, "word": "カレー", "spell_jp": "かれー", "spell_en": "karē", "name": "咖喱" }
            ,{ "id": 53, "word": "パン", "spell_jp": "ぱん", "spell_en": "pan", "name": "麵包" }
            ,{ "id": 54, "word": "料理", "spell_jp": "りょうり", "spell_en": "ryouri", "name": "料理" }
            ,{ "id": 55, "word": "魚", "spell_jp": "さかな", "spell_en": "sakana", "name": "魚" }
            ,{ "id": 56, "word": "肉", "spell_jp": "にく", "spell_en": "niku", "name": "肉" }
            ,{ "id": 57, "word": "牛肉", "spell_jp": "ぎゅうにく", "spell_en": "gyuuniku", "name": "牛肉" }
            ,{ "id": 58, "word": "豚肉", "spell_jp": "ぶたにく", "spell_en": "butaniku", "name": "豬肉" }
            ,{ "id": 59, "word": "鶏肉", "spell_jp": "とりにく", "spell_en": "toriniku", "name": "雞肉" }
            ,{ "id": 60, "word": "野菜", "spell_jp": "やさい", "spell_en": "yasai", "name": "蔬菜" }
            ,{ "id": 61, "word": "玉子、卵、たまご", "spell_jp": "たまご", "spell_en": "tamago", "name": "蛋" }
            ,{ "id": 62, "word": "醤油", "spell_jp": "しょうゆ", "spell_en": "shouyu", "name": "醬油" }
            ,{ "id": 63, "word": "飲み物", "spell_jp": "のみもの", "spell_en": "nomimono", "name": "飲料" }
            ,{ "id": 64, "word": "水", "spell_jp": "みず", "spell_en": "mizu", "name": "水" }
            ,{ "id": 65, "word": "お茶", "spell_jp": "おちゃ", "spell_en": "ocha", "name": "茶" }
            ,{ "id": 66, "word": "紅茶", "spell_jp": "こうちゃ", "spell_en": "koucha", "name": "紅茶" }
            ,{ "id": 67, "word": "牛乳", "spell_jp": "ぎゅうにゅう", "spell_en": "gyuunyuu", "name": "牛奶" }
            ,{ "id": 68, "word": "お酒", "spell_jp": "おさけ", "spell_en": "osake", "name": "酒" }
            ,{ "id": 69, "word": "ビール", "spell_jp": "びーる", "spell_en": "biiru", "name": "啤酒" }
            ,{ "id": 70, "word": "コーヒー", "spell_jp": "こーひー", "spell_en": "kōhī", "name": "咖啡" }
            ,{ "id": 71, "word": "ジュース", "spell_jp": "じゅーす", "spell_en": "jūsu", "name": "果汁" }
            ,{ "id": 72, "word": "服", "spell_jp": "ふく", "spell_en": "fuku", "name": "衣服" }
            ,{ "id": 73, "word": "洋服", "spell_jp": "ようふく", "spell_en": "youfuku", "name": "西裝" }
            ,{ "id": 74, "word": "コート", "spell_jp": "こーと", "spell_en": "kōto", "name": "外套" }
            ,{ "id": 75, "word": "シャツ", "spell_jp": "しゃつ", "spell_en": "shatsu", "name": "襯衫" }
            ,{ "id": 76, "word": "Tシャツ", "spell_jp": "Tしゃつ", "spell_en": "T-shatsu", "name": "T恤" }
            ,{ "id": 77, "word": "セーター", "spell_jp": "せーたー", "spell_en": "seetā", "name": "毛衣" }
            ,{ "id": 78, "word": "ポケット", "spell_jp": "ぽけっと", "spell_en": "poketto", "name": "口袋" }
            ,{ "id": 79, "word": "ボタン", "spell_jp": "ぼたん", "spell_en": "botan", "name": "鈕扣" }
            ,{ "id": 80, "word": "ズボン", "spell_jp": "ずぼん", "spell_en": "zubon", "name": "褲子" }
            ,{ "id": 81, "word": "半ズボン", "spell_jp": "はんズボン", "spell_en": "hanzubon", "name": "短褲" }
            ,{ "id": 82, "word": "スカート", "spell_jp": "すかーと", "spell_en": "sukāto", "name": "裙子" }
            ,{ "id": 83, "word": "靴", "spell_jp": "くつ", "spell_en": "kutsu", "name": "鞋子" }
            ,{ "id": 84, "word": "靴下", "spell_jp": "くつした", "spell_en": "kutsushita", "name": "襪子" }
            ,{ "id": 85, "word": "本", "spell_jp": "ほん", "spell_en": "hon", "name": "書籍" }
            ,{ "id": 86, "word": "紙", "spell_jp": "かみ", "spell_en": "kami", "name": "紙" }
            ,{ "id": 87, "word": "地図", "spell_jp": "ちず", "spell_en": "chizu", "name": "地圖" }
            ,{ "id": 88, "word": "辞書", "spell_jp": "じしょ", "spell_en": "jisho", "name": "辭典" }
            ,{ "id": 89, "word": "手紙", "spell_jp": "てがみ", "spell_en": "tegami", "name": "信件" }
            ,{ "id": 90, "word": "切手", "spell_jp": "きって", "spell_en": "kitte", "name": "郵票" }
            ,{ "id": 91, "word": "本棚", "spell_jp": "ほんだな", "spell_en": "hondana", "name": "書架" }
            ,{ "id": 92, "word": "鉛筆", "spell_jp": "えんぴつ", "spell_en": "enpitsu", "name": "鉛筆" }
            ,{ "id": 93, "word": "定規", "spell_jp": "じょうぎ", "spell_en": "jougi", "name": "尺規" }
            ,{ "id": 94, "word": "万年筆", "spell_jp": "まんねんひつ", "spell_en": "mannenhitsu", "name": "鋼筆" }
            ,{ "id": 95, "word": "消しゴム", "spell_jp": "けしゴム", "spell_en": "keshigomu", "name": "橡皮擦" }
            ,{ "id": 96, "word": "ペン", "spell_jp": "ぺん", "spell_en": "pen", "name": "筆" }
            ,{ "id": 97, "word": "ノート", "spell_jp": "のーと", "spell_en": "nōto", "name": "筆記本" }
            ,{ "id": 98, "word": "ボールペン", "spell_jp": "ぼーるぺん", "spell_en": "bōrupen", "name": "原子筆" }
            ,{ "id": 99, "word": "シャープペンシル", "spell_jp": "しゃーぷぺんしる", "spell_en": "shāpupenshiru", "name": "自動鉛筆" }
            ,{ "id": 100, "word": "電話", "spell_jp": "でんわ", "spell_en": "denwa", "name": "電話" }
            ,{ "id": 101, "word": "冷蔵庫", "spell_jp": "れいぞうこ", "spell_en": "reizouko", "name": "冰箱" }
            ,{ "id": 102, "word": "洗濯機", "spell_jp": "せんたくき", "spell_en": "sentakuki", "name": "洗衣機" }
            ,{ "id": 103, "word": "テレビ", "spell_jp": "てれび", "spell_en": "terebi", "name": "電視" }
            ,{ "id": 104, "word": "ビデオ", "spell_jp": "びでお", "spell_en": "bideo", "name": "錄影機" }
            ,{ "id": 105, "word": "カメラ", "spell_jp": "かめら", "spell_en": "kamera", "name": "相機" }
            ,{ "id": 106, "word": "パソコン", "spell_jp": "ぱそこん", "spell_en": "pasokon", "name": "電腦" }
            ,{ "id": 107, "word": "エアコン", "spell_jp": "えあこん", "spell_en": "eakon", "name": "空調" }
            ,{ "id": 108, "word": "上", "spell_jp": "うえ", "spell_en": "ue", "name": "上" }
            ,{ "id": 109, "word": "下", "spell_jp": "した", "spell_en": "shita", "name": "下" }
            ,{ "id": 110, "word": "左", "spell_jp": "ひだり", "spell_en": "hidari", "name": "左" }
            ,{ "id": 111, "word": "右", "spell_jp": "みぎ", "spell_en": "migi", "name": "右" }
            ,{ "id": 112, "word": "中", "spell_jp": "なか", "spell_en": "naka", "name": "中" }
            ,{ "id": 113, "word": "前", "spell_jp": "まえ", "spell_en": "mae", "name": "前" }
            ,{ "id": 114, "word": "後ろ", "spell_jp": "うしろ", "spell_en": "ushiro", "name": "後" }
            ,{ "id": 115, "word": "東", "spell_jp": "ひがし", "spell_en": "higashi", "name": "東" }
            ,{ "id": 116, "word": "西", "spell_jp": "にし", "spell_en": "nishi", "name": "西" }
            ,{ "id": 117, "word": "南", "spell_jp": "みなみ", "spell_en": "minami", "name": "南" }
            ,{ "id": 118, "word": "北", "spell_jp": "きた", "spell_en": "kita", "name": "北" }
            ,{ "id": 119, "word": "内", "spell_jp": "うち", "spell_en": "uchi", "name": "內部" }
            ,{ "id": 120, "word": "外", "spell_jp": "そと", "spell_en": "soto", "name": "外部" }
            ,{ "id": 121, "word": "辺", "spell_jp": "へん", "spell_en": "hen", "name": "周邊" }
            ,{ "id": 122, "word": "隣", "spell_jp": "となり", "spell_en": "tonari", "name": "旁邊" }
            ,{ "id": 123, "word": "横", "spell_jp": "よこ", "spell_en": "yoko", "name": "橫" }
            ,{ "id": 124, "word": "1", "spell_jp": "いち", "spell_en": "ichi", "name": "一" }
            ,{ "id": 125, "word": "2", "spell_jp": "に", "spell_en": "ni", "name": "二" }
            ,{ "id": 126, "word": "3", "spell_jp": "さん", "spell_en": "san", "name": "三" }
            ,{ "id": 127, "word": "4", "spell_jp": "よん", "spell_en": "yon", "name": "四" }
            ,{ "id": 128, "word": "5", "spell_jp": "ご", "spell_en": "go", "name": "五" }
            ,{ "id": 129, "word": "6", "spell_jp": "ろく", "spell_en": "roku", "name": "六" }
            ,{ "id": 130, "word": "7", "spell_jp": "しち/なな", "spell_en": "shichi/nana", "name": "七" }
            ,{ "id": 131, "word": "8", "spell_jp": "はち", "spell_en": "hachi", "name": "八" }
            ,{ "id": 132, "word": "9", "spell_jp": "きゅう", "spell_en": "kyuu", "name": "九" }
            ,{ "id": 133, "word": "10", "spell_jp": "じゅう", "spell_en": "juu", "name": "十" }
            ,{ "id": 134, "word": "会う", "spell_jp": "あう", "spell_en": "au", "name": "見面" }
            ,{ "id": 135, "word": "遊ぶ", "spell_jp": "あそぶ", "spell_en": "asobu", "name": "玩" }
            ,{ "id": 136, "word": "歩く", "spell_jp": "あるく", "spell_en": "aruku", "name": "走" }
            ,{ "id": 137, "word": "言う", "spell_jp": "いう", "spell_en": "iu", "name": "說" }
            ,{ "id": 138, "word": "行く", "spell_jp": "いく", "spell_en": "iku", "name": "去" }
            ,{ "id": 139, "word": "要る", "spell_jp": "いる", "spell_en": "iru", "name": "需要" }
            ,{ "id": 140, "word": "歌う", "spell_jp": "うたう", "spell_en": "utau", "name": "唱歌" }
            ,{ "id": 141, "word": "泳ぐ", "spell_jp": "およぐ", "spell_en": "oyogu", "name": "游泳" }
            ,{ "id": 142, "word": "終わる", "spell_jp": "おわる", "spell_en": "owaru", "name": "結束" }
            ,{ "id": 143, "word": "教える", "spell_jp": "おしえる", "spell_en": "oshieru", "name": "教導" }
            ,{ "id": 144, "word": "買う", "spell_jp": "かう", "spell_en": "kau", "name": "買" }
            ,{ "id": 145, "word": "書く", "spell_jp": "かく", "spell_en": "kaku", "name": "寫" }
            ,{ "id": 146, "word": "掛かる", "spell_jp": "かかる", "spell_en": "kakaru", "name": "掛上" }
            ,{ "id": 147, "word": "帰る", "spell_jp": "かえる", "spell_en": "kaeru", "name": "歸" }
            ,{ "id": 148, "word": "聞く", "spell_jp": "きく", "spell_en": "kiku", "name": "聽" }
            ,{ "id": 149, "word": "答える", "spell_jp": "こたえる", "spell_en": "kotaeru", "name": "回答" }
            ,{ "id": 150, "word": "困る", "spell_jp": "こまる", "spell_en": "komaru", "name": "困擾" }
            ,{ "id": 151, "word": "来る", "spell_jp": "くる", "spell_en": "kuru", "name": "來" }
            ,{ "id": 152, "word": "死ぬ", "spell_jp": "しぬ", "spell_en": "shinu", "name": "死" }
            ,{ "id": 153, "word": "住む", "spell_jp": "すむ", "spell_en": "sumu", "name": "住" }
            ,{ "id": 154, "word": "座る", "spell_jp": "すわる", "spell_en": "suwaru", "name": "坐" }
            ,{ "id": 155, "word": "立つ", "spell_jp": "たつ", "spell_en": "tatsu", "name": "立" }
            ,{ "id": 156, "word": "食べる", "spell_jp": "たべる", "spell_en": "taberu", "name": "吃" }
            ,{ "id": 157, "word": "疲れる", "spell_jp": "つかれる", "spell_en": "tsukareru", "name": "累" }
            ,{ "id": 158, "word": "着く", "spell_jp": "つく", "spell_en": "tsuku", "name": "到達" }
            ,{ "id": 159, "word": "使う", "spell_jp": "つかう", "spell_en": "tsukau", "name": "使用" }
            ,{ "id": 160, "word": "作る", "spell_jp": "つくる", "spell_en": "tsukuru", "name": "製作" }
            ,{ "id": 161, "word": "止まる", "spell_jp": "とまる", "spell_en": "tomaru", "name": "停" }
            ,{ "id": 162, "word": "飛ぶ", "spell_jp": "とぶ", "spell_en": "tobu", "name": "飛" }
            ,{ "id": 163, "word": "泣く", "spell_jp": "なく", "spell_en": "naku", "name": "哭" }
            ,{ "id": 164, "word": "寝る", "spell_jp": "ねる", "spell_en": "neru", "name": "睡" }
            ,{ "id": 165, "word": "乗る", "spell_jp": "のる", "spell_en": "noru", "name": "乘" }
            ,{ "id": 166, "word": "飲む", "spell_jp": "のむ", "spell_en": "nomu", "name": "喝" }
            ,{ "id": 167, "word": "違う", "spell_jp": "ちがう", "spell_en": "chigau", "name": "錯誤" }
            ,{ "id": 168, "word": "並ぶ", "spell_jp": "ならぶ", "spell_en": "narabu", "name": "排列" }
            ,{ "id": 169, "word": "入る", "spell_jp": "はいる", "spell_en": "hairu", "name": "進入" }
            ,{ "id": 170, "word": "始まる", "spell_jp": "はじまる", "spell_en": "hajimaru", "name": "開始" }
            ,{ "id": 171, "word": "走る", "spell_jp": "はしる", "spell_en": "hashiru", "name": "奔跑" }
            ,{ "id": 172, "word": "降る", "spell_jp": "ふる", "spell_en": "furu", "name": "下" }
            ,{ "id": 173, "word": "見る", "spell_jp": "みる", "spell_en": "miru", "name": "看" }
            ,{ "id": 174, "word": "待つ", "spell_jp": "まつ", "spell_en": "matsu", "name": "等" }
            ,{ "id": 175, "word": "読む", "spell_jp": "よむ", "spell_en": "yomu", "name": "讀" }
            ,{ "id": 176, "word": "暑い", "spell_jp": "あつい", "spell_en": "atsui", "name": "熱" }
            ,{ "id": 177, "word": "寒い", "spell_jp": "さむい", "spell_en": "samui", "name": "冷" }
            ,{ "id": 178, "word": "冷たい", "spell_jp": "つめたい", "spell_en": "tsumetai", "name": "冷" }
            ,{ "id": 179, "word": "暖かい", "spell_jp": "あたたかい", "spell_en": "atatakai", "name": "溫" }
            ,{ "id": 180, "word": "涼しい", "spell_jp": "すずしい", "spell_en": "suzushii", "name": "涼" }
            ,{ "id": 181, "word": "厚い", "spell_jp": "あつい", "spell_en": "atsui", "name": "厚" }
            ,{ "id": 182, "word": "薄い", "spell_jp": "うすい", "spell_en": "usui", "name": "薄" }
            ,{ "id": 183, "word": "近い", "spell_jp": "ちかい", "spell_en": "chikai", "name": "近" }
            ,{ "id": 184, "word": "遠い", "spell_jp": "とおい", "spell_en": "tooi", "name": "遠" }
            ,{ "id": 185, "word": "弱い", "spell_jp": "よわい", "spell_en": "yowai", "name": "弱" }
            ,{ "id": 186, "word": "強い", "spell_jp": "つよい", "spell_en": "tsuyoi", "name": "強" }
            ,{ "id": 187, "word": "軽い", "spell_jp": "かるい", "spell_en": "karui", "name": "輕" }
            ,{ "id": 188, "word": "重い", "spell_jp": "おもい", "spell_en": "omoi", "name": "重" }
            ,{ "id": 189, "word": "狭い", "spell_jp": "せまい", "spell_en": "semai", "name": "窄" }
            ,{ "id": 190, "word": "広い", "spell_jp": "ひろい", "spell_en": "hiroi", "name": "寬" }
            ,{ "id": 191, "word": "早い", "spell_jp": "はやい", "spell_en": "hayai", "name": "早" }
            ,{ "id": 192, "word": "速い", "spell_jp": "はやい", "spell_en": "hayai", "name": "快" }
            ,{ "id": 193, "word": "遅い", "spell_jp": "おそい", "spell_en": "osoi", "name": "慢" }
            ,{ "id": 194, "word": "太い", "spell_jp": "ふとい", "spell_en": "futoi", "name": "粗" }
            ,{ "id": 195, "word": "細い", "spell_jp": "ほそい", "spell_en": "hosoi", "name": "細" }
            ,{ "id": 196, "word": "短い", "spell_jp": "みじかい", "spell_en": "mijikai", "name": "短" }
            ,{ "id": 197, "word": "長い", "spell_jp": "ながい", "spell_en": "nagai", "name": "長" }
            ,{ "id": 198, "word": "安い", "spell_jp": "やすい", "spell_en": "yasui", "name": "便宜" }
            ,{ "id": 199, "word": "高い", "spell_jp": "たかい", "spell_en": "takai", "name": "高" }
            ,{ "id": 200, "word": "低い", "spell_jp": "ひくい", "spell_en": "hikui", "name": "低" }
            ,{ "id": 201, "word": "良い", "spell_jp": "いい", "spell_en": "ii", "name": "好" }
            ,{ "id": 202, "word": "悪い", "spell_jp": "わるい", "spell_en": "warui", "name": "壞" }
            ,{ "id": 203, "word": "汚い", "spell_jp": "きたない", "spell_en": "kitanai", "name": "骯髒" }
            ,{ "id": 204, "word": "新しい", "spell_jp": "あたらしい", "spell_en": "atarashii", "name": "新" }
            ,{ "id": 205, "word": "古い", "spell_jp": "ふるい", "spell_en": "furui", "name": "舊" }
            ,{ "id": 206, "word": "面白い", "spell_jp": "おもしろい", "spell_en": "omoshiroi", "name": "有趣" }
            ,{ "id": 207, "word": "つまらない", "spell_jp": "つまらない", "spell_en": "tsumaranai", "name": "無聊" }
            ,{ "id": 208, "word": "大きい", "spell_jp": "おおきい", "spell_en": "ookii", "name": "大" }
            ,{ "id": 209, "word": "小さい", "spell_jp": "ちいさい", "spell_en": "chiisai", "name": "小" }
            ,{ "id": 210, "word": "甘い", "spell_jp": "あまい", "spell_en": "amai", "name": "甜" }
            ,{ "id": 211, "word": "美味しい", "spell_jp": "おいしい", "spell_en": "oishii", "name": "好吃" }
            ,{ "id": 212, "word": "まずい", "spell_jp": "まずい", "spell_en": "mazui", "name": "不好吃" }
            ,{ "id": 213, "word": "易しい", "spell_jp": "やさしい", "spell_en": "yasashii", "name": "容易" }
            ,{ "id": 214, "word": "難しい", "spell_jp": "むずかしい", "spell_en": "muzukashii", "name": "難" }
            ,{ "id": 215, "word": "優しい", "spell_jp": "やさしい", "spell_en": "yasashii", "name": "溫柔" }
            ,{ "id": 216, "word": "厳しい", "spell_jp": "きびしい", "spell_en": "kibishii", "name": "嚴厲" }
            ,{ "id": 217, "word": "暗い", "spell_jp": "くらい", "spell_en": "kurai", "name": "暗" }
            ,{ "id": 218, "word": "明るい", "spell_jp": "あかるい", "spell_en": "akarui", "name": "明" }
            ,{ "id": 219, "word": "忙しい", "spell_jp": "いそがしい", "spell_en": "isogashii", "name": "忙" }
            ,{ "id": 220, "word": "可愛い", "spell_jp": "かわいい", "spell_en": "kawaii", "name": "可愛" }
            ,{ "id": 221, "word": "楽しい", "spell_jp": "たのしい", "spell_en": "tanoshii", "name": "快樂" }
            ,{ "id": 222, "word": "うるさい", "spell_jp": "うるさい", "spell_en": "urusai", "name": "吵" }
            ,{ "id": 223, "word": "綺麗", "spell_jp": "きれい", "spell_en": "kirei", "name": "美麗" }
            ,{ "id": 224, "word": "便利", "spell_jp": "べんり", "spell_en": "benri", "name": "方便" }
            ,{ "id": 225, "word": "有名", "spell_jp": "ゆうめい", "spell_en": "yuumei", "name": "有名" }
            ,{ "id": 226, "word": "静か", "spell_jp": "しずか", "spell_en": "shizuka", "name": "安靜" }
            ,{ "id": 227, "word": "賑やか", "spell_jp": "にぎやか", "spell_en": "nigiyaka", "name": "熱鬧" }
        ]
        , test_words: []
        , all_test_words: []

        , forget_words: []
        , remember_words: []

        , test_headers: [
            { text: '題目', value: 'test', sortable: false },
            { text: '輸入', value: 'input', sortable: false },
            { text: '完成', value: 'actions', sortable: false, align: 'center' },
        ]
        , headers: [
            { text: '日文', value: 'word', sortable: false },
            { text: '日文拼音', value: 'spell_jp' },
            { text: '英文拼音', value: 'spell_en' },
            { text: '名稱', value: 'name', sortable: false },
            { text: '操作', value: 'actions', sortable: false, align: 'center' },
        ]
        , rules: [
            value => !!value || 'Required.',
            value => (value && value.length >= 3) || 'Min 3 characters',
        ]
    },
    computed: {
        // 暫不支援 computed
        get_question_word() {
            let randomIndex = Math.floor(Math.random() * this.forget_words.length);
            let randomItem = this.forget_words[randomIndex];
            return randomItem;
        },
    },
    created() {
        this.init();
    },
    methods: {
        init() {
            //
            this.forget_words = this.words;
            this.postQuestion();
        }
        , input_compositionend(question_type, index) {
            this.is_can_ans_check = true;
            question_type ? this.updateForgetWrodValue(index) : this.updateValue(index);
        }
        , input_compositionstart() {
            this.is_can_ans_check = false;
        }
        , updateForgetWrodValue(index) {
            if(!this.is_can_ans_check) return;
            if(this.inputAns[index] != this.test_words[index]['spell_en'] && this.inputAns[index] != this.test_words[index]['spell_jp']) return;
            this.addRemember(this.test_words[index]['item']);
            this.postQuestion();
        }
        , updateValue(index) {
            // 及時處裡
            // let ans = this.inputAns.split("\n");
            // ans = ans.filter( item => item != '');
            if(!this.is_can_ans_check) return;
            if(this.inputAns[index] != this.test_words[index]['spell_en'] && this.inputAns[index] != this.test_words[index]['spell_jp']) return;
            this.addRemember(this.test_words[index]['item']);
            this.postQuestion();
        }
        // todo: 完善 全部出題 與 遺忘出題
        , postQuestion() {
            if(this.forget_words.length <= 0) return;
            let randomIndex = Math.floor(Math.random() * this.forget_words.length);
            let randomItem = this.forget_words[randomIndex];
            this.test_words.splice(0, 0, { test: randomItem['word'] , input: '', action: '', spell_en: randomItem['spell_en'], spell_jp: randomItem['spell_jp'], item: randomItem });

            this.inputAns.splice(0, 0, '');
            // 太長 刪除
            if(this.inputAns.length > 5) {
                this.test_words.pop();
                this.inputAns.pop();
            }
        }
        , getAns(index) {
            // 給出答案
            this.inputAns[index] = `${this.test_words[index]['spell_jp']}/${this.test_words[index]['spell_en']} (${this.test_words[index]['item']['name']})`;
            // 放遺忘
            this.addForget(this.test_words[index]['item']);
            // 產新問題
            this.postQuestion();
        }
        , addForget(forget_word){
            let exists = this.forget_words.some(item => item.id === forget_word.id);
            if(!exists) this.forget_words.push(forget_word);
        }
        , addRemember(remember_word){
            let exists = this.remember_words.some(item => item.id === remember_word.id);
            if(!exists) this.remember_words.push(remember_word);
        }
        , removeForget(item, key){
            this.forget_words.splice(key, 1)
            let new_item = { id: item.id, word: item.word, spell_jp: item.spell_jp, spell_en: item.spell_en, name: item.name };
            this.remember_words.push(new_item);
        }
        , removeRemember(item, key){
            this.remember_words.splice(key, 1)
            let new_item = { id: item.id, word: item.word, spell_jp: item.spell_jp, spell_en: item.spell_en, name: item.name };
            this.forget_words.push(new_item);
        }
        , filterSearch (value, search, item) {
            let regex = new RegExp(`${search}`);
            if(search === null || value === null || !search) return true;
            if(item.word.search(regex) >= 0) return true;
            if(item.spell.search(regex) >= 0) return true;
            if(item.name.search(regex) >= 0) return true;
        }
        , settingExport() {
            let setting_json = {};
            setting_json.forget_words = this.forget_words;
            setting_json.remember_words = this.remember_words;
            this.setting_json = JSON.stringify(setting_json);
        }
        , settingImport() {
            let setting_json = JSON.parse(this.setting_json)
            this.forget_words = setting_json.forget_words;
            this.remember_words = setting_json.remember_words;
        }
    },
    watch: {
    },
});

</script>
</html>
