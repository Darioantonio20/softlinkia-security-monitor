---
name: Desarrollo de Interfaces "Ultra Premium"
description: Estándares de diseño de vanguardia para crear interfaces que asombren (Efectos Liquid, Mesh Gradients y Contraste Perfecto).
---

# Guía de Estética de Vanguardia (Ultra-Premium)

Esta guía define el estándar mínimo para Softlinkia. No se aceptan diseños "básicos" o genéricos.

## 1. Dominio Visual y Contraste
- **Legibilidad Crítica**: NUNCA usar texto blanco sobre fondos claros o grises. El texto debe tener un contraste de al menos 4.5:1. Usa `text-slate-900` o `text-gray-800` en áreas claras.
- **Tipografía**: Fuente **Outfit** o **Inter**. Usa `tracking-tighter` para títulos grandes y `tracking-widest` para labels pequeños en mayúsculas.
- **Profundidad de Capas**: Usa `backdrop-blur-xl` combinado con bordes semi-transparentes (`border-white/20`) y sombras suaves (`shadow-[0_20px_50px_rgba(0,0,0,0.1)]`).

## 2. Paleta de Colores y Efectos "Liquid"
- **Mesh Gradients**: Usa gradientes radiales múltiples en el fondo para crear profundidad dinámica. No uses colores planos.
- **Liquid Glass**: El glassmorphism debe sentirse como cristal líquido. Usa `bg-white/40` con `backdrop-blur-2xl` y reflejos internos.
- **Acentos Vibrantes**: Usa colores HSL para estados: 
  - Éxito: `hsl(142, 70%, 45%)` (Emerald vibrante)
  - Alerta: `hsl(346, 84%, 50%)` (Rose profundo)
  - Acción: `hsl(250, 89%, 60%)` (Indigo eléctrico)

## 3. Micro-interacciones de Lujo
- **Hovers**: No solo cambies el color. Usa `hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300`.
- **Efectos de Brillo**: Los botones deben tener un "glow" sutil (`shadow-indigo-500/40`).
- **Scroll Personalizado**: Usa scrollbars finas y minimalistas que se integren con el diseño.

## 4. Estándares Técnicos y Corporativos Pro
- **Iconografía Profesional**: QUEDA PROHIBIDO el uso de Emojis en la interfaz. Usa exclusivamente iconos vectoriales (SVG) de estilo minimalista y corporativo (Heroicons, Lucide).
- **Tablas Adaptativas "No-Scroll"**: QUEDA PROHIBIDO el scroll horizontal en tablas para dispositivos móviles. En su lugar, utiliza layouts basados en tarjetas (`card-based layout`) que se activen mediante media queries (`hidden md:table` / `block md:hidden`).
- **Valores Arbitrarios**: Usa valores arbitrarios de Tailwind `[]` para ajustes de precisión quirúrgica.
- **SEO & Semántica**: Cada vista debe tener Títulos H1 únicos y descriptivos.

## 5. REGLA DE ORO: EL EFECTO "WOW" CORPORATIVO
Si al ver la interfaz por primera vez no sientes que es un producto de software empresarial de alto nivel ($10,000 USD+), entonces has FALLADO. Cada píxel debe ser profesional, sobrio y estéticamente superior.
