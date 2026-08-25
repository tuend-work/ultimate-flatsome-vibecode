# Hướng Dẫn Định Kiểu CSS Responsive Trong VBC Elements

Quy chuẩn viết CSS Selector & Media Queries cho VBC Elements:

---

## 1. Breakpoints Chuẩn Flatsome

Flatsome và VBC Elements sử dụng 3 mốc breakpoints chuẩn:
- **Desktop**: Màn hình rộng $> 849px$.
- **Tablet**: `@media(max-width: 849px)`.
- **Mobile**: `@media(max-width: 549px)`.

---

## 2. Cú Pháp Grid Layout Mẫu

```css
selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}
@media(max-width: 849px) {
    selector {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}
@media(max-width: 549px) {
    selector {
        grid-template-columns: 1fr;
    }
}
```
