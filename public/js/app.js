/**
 * [event] ページ読み込み完了時
 */
window.addEventListener('load', async ()=>{
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
});


/**
 * 商品一覧を描画する
 *
 * @returns {void|false}
 */
async function renderProductList(products=null) {
  const list   = document.querySelector('#product-list');   // 商品一覧の親要素
  const source = document.querySelector('#template-product-list-item'); // handlebarsのテンプレート
  const template = Handlebars.compile(source.innerHTML);

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