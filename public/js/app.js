/**
 * [event] ページ読み込み完了時
 */
window.addEventListener('load', async ()=>{
  //---------------------------------------------
  // 商品一覧
  //---------------------------------------------
  // 最初の商品一覧を描画
  renderProductList();

  // 商品絞り込みタブのクリック時
  const tabEl = document.querySelectorAll('button[data-bs-toggle="pill"]');
  tabEl.forEach((element)=>{
    element.addEventListener('click', async ()=>{
      const category = element.getAttribute('data-category');
      const products = await getProductList(category);
      renderProductList(products);
    });
  });

  //---------------------------------------------
  // 新規登録ダイアログ
  //---------------------------------------------
  // 登録ボタンクリック時
  document.querySelector('#btn-join').addEventListener('click', ()=>{
    document.querySelector('#dialog-join').showModal(); // ダイアログを開く
    document.querySelector('#join-nickname').focus();   // ニックネームをフォーカス
  });
  // 閉じるボタン
  document.querySelector('#btn-join-close').addEventListener('click', ()=>{
    document.querySelector('#dialog-join').close();
  });
  // 登録ボタン
  document.querySelector('#btn-join-submit').addEventListener('click', userJoin);
});


/**
 * ユーザー登録処理
 *
 */
async function userJoin(){
  // 入力された値を取得
  const nickname = document.querySelector('#join-nickname');
  const loginid  = document.querySelector('#join-loginid');
  const password = document.querySelector('#join-password');

  //---------------------------------------------
  // バリデーション
  //---------------------------------------------
  if( nickname.value === '' ) {
    alert('ニックネームを入力してください');
    nickname.focus();
    return(false);
  }
  if( loginid.value === '' ) {
    alert('ログインIDを入力してください');
    loginid.focus();
    return(false);
  }
  if( password.value === '' ) {
    alert('パスワードを入力してください');
    password.focus();
    return(false);
  }

  // 最終確認
  if( ! confirm('本当に登録しますか？') ) {
    return(false);
  }

  //---------------------------------------------
  // APIに送信
  //---------------------------------------------
  // 送信するデータを準備
  const params = {
    nickname : nickname.value,
    loginid  : loginid.value,
    password : password.value
  };

  const json = await fetchApi('api/user/join.php', params, 'POST');
  console.log('[userJoin] json', json);
  if(  'status' in json && json.status === true ) {
    alert('登録に成功しました');
    location.reload();
  }
}

/**
 * 商品一覧を描画する
 *
 * @returns {void|false}
 */
async function renderProductList(products=null) {
  const list   = document.querySelector('#product-list');   // 商品一覧の親要素
  const source = document.querySelector('#template-product-list-item'); // handlebarsのテンプレート
  const template = Handlebars.compile(source.innerHTML);
  Handlebars.registerHelper('showCategory', (category, option)=>{
    const list = {
      'TWS': '両手',
      'GUN': '銃',
      'ARM': '腕',
      'MAG': '魔法'
    }
    if( category in list ) {
      return list[category];
    }
    return '?';
  });
  //---------------------------------------------
  // APIから商品一覧を取得
  //---------------------------------------------
  // リクエスト送信
  if( products === null ){
    products = await getProductList();
  }
  console.log('[renderProductList] products', products);

  // 商品がない場合はアラートを表示し終了
  if( !('items' in products) || !Array.isArray(products.items) || products.items.length === 0) {
    alert('商品がありません');
    return(false);
  }

  //---------------------------------------------
  // 画面に描画する
  //---------------------------------------------
  // handlebarsで描画
  const html = template(products);
  list.innerHTML = html;

  // 「買う」ボタンのイベント設定
  document.querySelectorAll('.btn-buy').forEach((btn)=>{
    btn.addEventListener('click', ()=>{
      const id = btn.getAttribute('data-id');
      alert(`ToDo: id=${id}を購入する処理を実装`);
    });
  });
}

/**
 * REST APIから商品一覧を取得する
 *
 * @param {string} [category=null] - 商品カテゴリ
 * @return {Promise} 商品一覧
 */
function getProductList(category=null) {
  // カテゴリー未指定（初回表示時）
  if( category === null || category === '' ) {
    return fetchApi('api/item/list.php');
  }

  // カテゴリー指定（絞り込み時）
  return fetchApi('api/item/category.php', {cd:category});
}

/**
 * REST APIからデータを取得する汎用関数
 *
 * @param {string} endpoint - リクエスト先のURL
 * @param {object} [query=null] - リクエストボディ
 * @param {string} [method='GET'] - リクエストメソッド
 * @return {Promise}
 */
async function fetchApi(endpoint, query=null, method='GET') {
  let url = endpoint;
  const params = {
    method     // method: method と同じ意味
  };

  //---------------------------------------------
  // メソッド毎に通信内容の設定
  //---------------------------------------------
  // POST
  if( query !== null && method === 'POST' ) {
    params.body = new URLSearchParams(query);
    params.headers = {};
    params.headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }
  // GET
  else if( query !== null && method === 'GET' ) {
    url += '?' + new URLSearchParams(query);
  }
  console.log('[fetchApi] URL', url);
  console.log('[fetchApi] params', params);

  //---------------------------------------------
  // リクエスト送信
  //---------------------------------------------
  const response = await fetch(url, params);
  console.log('[fetchApi] response', response);

  // エラーチェック
  if ( ! response.ok ) {
    console.error('[fetchApi] !response.ok', response);
    throw new Error(`${response.status} ${response.statusText}`);
  }

  // JSON形式に変換して返却する
  return response.json();
}