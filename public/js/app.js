/**
 * [event] ページ読み込み完了時
 */
window.addEventListener('load', async ()=>{
  renderProductList();
});


/**
 * 商品一覧を描画する
 *
 * @returns {void|false}
 */
async function renderProductList() {
  const list   = document.querySelector('#product-list');   // 商品一覧の親要素
  const source = document.querySelector('#template-product-list-item'); // handlebarsのテンプレート
  const template = Handlebars.compile(source.innerHTML);

  //---------------------------------------------
  // APIから商品一覧を取得
  //---------------------------------------------
  // リクエスト送信
  const products = await getProductList();
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
 * @return {Promise} 商品一覧
 */
function getProductList() {
  return fetchApi('/api/item/list.php');
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