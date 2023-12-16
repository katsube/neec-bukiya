const User = {
  session_id: null,
};
const Product = {
  items: [ ],
  findById: (id)=>{
    const items = Product.items;
    const value = Number(id);
    for(let i=0; i<items.length; i++){
      if( items[i].id === value ){
        return items[i];
      }
    }
    return null;
  }
};

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
  const tabEl = qAll('button[data-bs-toggle="pill"]');
  tabEl.forEach((element)=>{
    element.addEventListener('click', async ()=>{
      const category = element.getAttribute('data-category');
      const products = await getProductList(category);
      renderProductList(products);
    });
  });

  //---------------------------------------------
  // ログイン状態をチェック
  //---------------------------------------------
  const session_id = localStorage.getItem('session_id');
  if( session_id !== null ) {
    User.session_id = session_id;
    q('#btn-inventory').style.display = 'block';    // 所有アイテムボタンを表示
    q('#btn-logout').style.display = 'block'; // ログアウトボタンを表示
  }
  else{
    q('#btn-join').style.display = 'block';   // 新規登録ボタンを表示
    q('#btn-login').style.display = 'block';  // ログインボタンを表示
  }

  //---------------------------------------------
  // 新規登録ダイアログ
  //---------------------------------------------
  // 登録ボタンクリック時
  q('#btn-join').addEventListener('click', ()=>{
    q('#dialog-join').showModal(); // ダイアログを開く
    q('#join-nickname').focus();   // ニックネームをフォーカス
  });
  // 閉じるボタン
  q('#btn-join-close').addEventListener('click', ()=>{
    q('#dialog-join').close();
  });
  // 登録ボタン
  q('#btn-join-submit').addEventListener('click', userJoin);

  //---------------------------------------------
  // ログインダイアログ
  //---------------------------------------------
  // ログインボタンクリック時
  q('#btn-login').addEventListener('click', ()=>{
    q('#dialog-login').showModal(); // ダイアログを開く
    q('#loginid').focus();    // ログインIDをフォーカス
  });
  // 閉じるボタン
  q('#btn-login-close').addEventListener('click', ()=>{
    q('#dialog-login').close();
  });
  // ログインボタン
  q('#btn-login-submit').addEventListener('click', userLogin);

  //---------------------------------------------
  // ログアウトボタン
  //---------------------------------------------
  // ログアウトボタンクリック時
  q('#btn-logout').addEventListener('click', userLogout);

  //---------------------------------------------
  // 所有アイテムボタン
  //---------------------------------------------
  // 所有アイテムボタンクリック時
  q('#btn-inventory').addEventListener('click', userInventory);
});


/*
 * [wrapper] document.querySelector()
 */
function q(selector) {
  return document.querySelector(selector);
}

/**
 * [wrapper] document.querySelectorAll()
 */
function qAll(selector) {
  return document.querySelectorAll(selector);
}

/**
 * ユーザー登録処理
 *
 */
async function userJoin(){
  // 入力された値を取得
  const nickname = q('#join-nickname');
  const loginid  = q('#join-loginid');
  const password = q('#join-password');

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
    location.reload();    // ページを再読込み
  }
}

/**
 * ログイン
 *
 */
async function userLogin(){
  // 入力された値を取得
  const loginid  = q('#loginid');
  const password = q('#password');

  //---------------------------------------------
  // バリデーション
  //---------------------------------------------
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

  //---------------------------------------------
  // APIに送信
  //---------------------------------------------
  // 送信するデータを準備
  const params = {
    loginid  : loginid.value,
    password : password.value
  };

  const json = await fetchApi('api/user/login.php', params, 'POST');
  console.log('[userLogin] json', json);
  if( 'status' in json && json.status === true ) {
    const session_id = json.session_id;
    localStorage.setItem('session_id', session_id);
    location.reload();    // ページを再読込み
  }
  else{
    alert('ログインに失敗しました');
  }
}

/**
 * ログアウト
 *
 */
async function userLogout(){
  if( ! confirm('ログアウトしますか？') ){
    return(false);
  }

  // 送信するデータを準備
  const params = {
    session_id: User.session_id
  };

  // ログアウトAPIに送信
  const json = await fetchApi('api/user/logout.php', params, 'POST');
  console.log('[userLogout] json', json);
  if( 'status' in json && json.status === true ) {
    alert('ログアウトしました\n（ページを再読込みします）');
    localStorage.removeItem('session_id');    // ローカルストレージから削除
    location.reload();    // ページを再読込み
  }
  else{
    alert('ログアウトに失敗しました');
  }
}

/**
 * 所有アイテム一覧を表示する
 *
 */
function userInventory(){
  // 未ログイン時はログインダイアログを表示
  if( User.session_id === null ) {
    q('#dialog-login').showModal();
    return;
  }

  // 所有アイテム一覧を描画
  renderInventoryList();

  // ダイアログを開く
  q('#dialog-inventory').showModal();
}

/**
 * 商品一覧を描画する
 *
 * @returns {void|false}
 */
async function renderProductList(products=null) {
  const list   = q('#product-list');   // 商品一覧の親要素
  const source = q('#template-product-list-item'); // handlebarsのテンプレート
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

  //-----------------------------
  // 「買う」ボタン
  //-----------------------------
  qAll('.btn-buy').forEach((btn)=>{
    btn.addEventListener('click', ()=>{
      // 未ログイン時はログインダイアログを表示
      if( User.session_id === null ) {
        q('#dialog-login').showModal();
        return;
      }

      // 商品購入ダイアログを表示
      const id = btn.getAttribute('data-id');
      renderBuyDialog(id);
    });
  });
}


/**
 * 商品購入ダイアログを描画する
 *
 * @param {number} product_id 商品ID
 */
async function renderBuyDialog(product_id){
  const dialog = q('#dialog-buy');
  const item = Product.findById(product_id);

  //---------------------------------------------
  // 表示要素を設定
  //---------------------------------------------
  // 商品画像
  q('#dialog-buy img').src = `image/weapon/${item.image}`;

  // 商品名
  q('#dialog-buy .product-name').textContent = item.name;

  //---------------------------------------------
  // 購入ボタン
  //---------------------------------------------
  const btn = q('#btn-buy-submit');
  const btnClick = async ()=>{
    const result = await requestBuyProduct(product_id);
    if( result === true ) {
      alert('購入しました');
    }
    else{
      alert('購入に失敗しました');
    }
    dialog.close();
  };

  // 購入ボタン押下時
  btn.addEventListener('click', btnClick);

  //---------------------------------------------
  // [event] 閉じるボタン
  //---------------------------------------------
  q('#btn-buy-close').addEventListener('click', ()=>{
    dialog.close();
  });

  //---------------------------------------------
  // [event] ダイアログを閉じた時
  //---------------------------------------------
  dialog.addEventListener('close', ()=>{
    // 購入ボタンのイベントを削除
    btn.removeEventListener('click', btnClick);
  });

  //---------------------------------------------
  // ダイアログを開く
  //---------------------------------------------
  dialog.showModal();
  q('#btn-buy-submit').focus(); // フォーカスを購入ボタンに移動
}

/**
 * 所有アイテム一覧を描画する
 *
 */
async function renderInventoryList(){
  const list   = q('#inventory-list');   // 商品一覧の親要素
  const source = q('#template-inventory-list'); // handlebarsのテンプレート
  const template = Handlebars.compile(source.innerHTML);

  //---------------------------------------------
  // APIから商品一覧を取得
  //---------------------------------------------
  const inventory = await getInventoryList();
  console.log('[renderInventoryList] inventory ', inventory);

  if( !('data' in inventory) || !Array.isArray(inventory.data) ) {
    alert('正常に取得できませんでした');
    return(false);
  }

  // 所有アイテムがない場合はdataを削除
  if( inventory.data.length === 0 ) {
    delete inventory.data;
  }

  //---------------------------------------------
  // 画面に描画する
  //---------------------------------------------
  // handlebarsで描画
  const html = template(inventory);
  list.innerHTML = html;

  //---------------------------------------------
  // [event] 閉じるボタン
  //---------------------------------------------
  q('#btn-inventory-close').addEventListener('click', ()=>{
    q('#dialog-inventory').close();
  });
  q('#btn-inventory-close-bottom').addEventListener('click', ()=>{
    q('#dialog-inventory').close();
  });
}


/**
 * REST APIから商品一覧を取得する
 *
 * @param {string} [category=null] - 商品カテゴリ
 * @return {Promise} 商品一覧
 */
async function getProductList(category=null) {
  let response;

  // カテゴリー未指定（初回表示時）
  if( category === null || category === '' ) {
    response = await fetchApi('api/item/list.php');
  }
  // カテゴリー指定（絞り込み時）
  else{
    response = await fetchApi('api/item/category.php', {cd:category});
  }

  // グローバル変数に格納
  Product.items = response.items;
  return response;
}

/**
 * 所有アイテム一覧を取得する
 *
 */
async function getInventoryList(){
  // 送信するデータを準備
  const params = {
    session_id: User.session_id
  };

  // 所有アイテム一覧APIに送信
  const json = await fetchApi('api/user/inventory.php', params, 'GET');
  console.log('[getInventoryList] json', json);
  return json;
}

/**
 * 商品購入APIを呼び出す
 *
 * @param {number} product_id 商品ID
 * @returns {boolean}
 */
async function requestBuyProduct(product_id){
  // 送信するデータを準備
  const params = {
    session_id: User.session_id,
    product_id
  };

  // 商品購入APIに送信
  const json = await fetchApi('api/cart/buy.php', params, 'GET');
  console.log('[userBuyProduct] json', json);
  return ( ('status' in json) && (json.status === true) );
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