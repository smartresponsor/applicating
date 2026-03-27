/** Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp */
export class ProductSDK {
  constructor(private baseUrl: string, private token?: string) {}
  async getProduct(id: string) {
    const r = await fetch(`${this.baseUrl}/product/${id}`, { headers: this.headers() });
    return r.json();
  }
  async createProduct(p: any) {
    const r = await fetch(`${this.baseUrl}/product`, { method: "POST", headers: this.headers(), body: JSON.stringify(p) });
    return r.json();
  }
  async subscribeWebhook(url: string, secret: string) {
    const r = await fetch(`${this.baseUrl}/product/webhook/subscribe`, { method: "POST", headers: this.headers(), body: JSON.stringify({url, secret}) });
    return r.json();
  }
  private headers() {
    const h: any = {"Content-Type":"application/json"};
    if (this.token) h["Authorization"] = `Bearer ${this.token}`;
    return h;
  }
}
