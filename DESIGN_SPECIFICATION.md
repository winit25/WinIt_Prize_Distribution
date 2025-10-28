# 🎨 BuyPower App Design Specification

## **Color Palette**

### **Primary Colors**
```css
--primary-blue: #1e3a8a;        /* Deep Navy Blue */
--secondary-blue: #3b82f6;      /* Bright Blue */
--accent-green: #10b981;        /* Emerald Green */
```

### **Text Colors**
```css
--text-dark: #1f2937;           /* Dark Gray */
--text-gray: #6b7280;           /* Medium Gray */
```

### **Background Colors**
```css
--bg-light: #f8fafc;            /* Very Light Gray */
--border-light: #e5e7eb;        /* Light Border Gray */
```

### **Status Colors**
```css
--success-bg: #d1fae5;          /* Light Green Background */
--success-text: #065f46;        /* Dark Green Text */
--danger-bg: #fecaca;           /* Light Red Background */
--danger-text: #991b1b;         /* Dark Red Text */
--info-bg: #dbeafe;             /* Light Blue Background */
--info-text: #1e40af;           /* Dark Blue Text */
--warning-bg: #fef3c7;          /* Light Yellow Background */
--warning-text: #d97706;        /* Dark Orange Text */
```

## **Typography**

### **Font Family**
```css
font-family: 'Figtree', sans-serif;
```

### **Font Weights**
- **Regular**: 400
- **Medium**: 500
- **Semi-bold**: 600
- **Bold**: 700

## **Layout & Spacing**

### **Border Radius**
```css
--border-radius-small: 0.75rem;     /* 12px */
--border-radius-medium: 1rem;       /* 16px */
--border-radius-large: 1.5rem;      /* 24px */
--border-radius-xl: 2rem;           /* 32px */
```

### **Padding & Margins**
```css
--padding-small: 0.75rem;           /* 12px */
--padding-medium: 1rem;             /* 16px */
--padding-large: 1.5rem;            /* 24px */
--padding-xl: 2rem;                 /* 32px */
--padding-xxl: 3rem;                /* 48px */
```

## **Component Styles**

### **Cards**
```css
.card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border-light);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.card:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
```

### **Card Headers**
```css
.card-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: white;
    border-radius: 1.5rem 1.5rem 0 0;
    padding: 1.5rem 2rem;
    border: none;
}
```

### **Buttons**
```css
.btn {
    border-radius: 0.75rem;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    color: white;
}
```

### **Form Controls**
```css
.form-control {
    border: 2px solid var(--border-light);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--bg-light);
}

.form-control:focus {
    border-color: var(--secondary-blue);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background: white;
}
```

### **Badges**
```css
.badge {
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.badge-success {
    background: var(--success-bg);
    color: var(--success-text);
}

.badge-danger {
    background: var(--danger-bg);
    color: var(--danger-text);
}

.badge-info {
    background: var(--info-bg);
    color: var(--info-text);
}
```

### **Alerts**
```css
.alert {
    border-radius: 1rem;
    border: none;
    padding: 1.25rem;
    font-weight: 500;
}

.alert-success {
    background: var(--success-bg);
    color: var(--success-text);
    border-left: 4px solid var(--accent-green);
}

.alert-danger {
    background: var(--danger-bg);
    color: var(--danger-text);
    border-left: 4px solid #dc2626;
}

.alert-info {
    background: var(--info-bg);
    color: var(--info-text);
    border-left: 4px solid var(--secondary-blue);
}
```

## **Sidebar Design**

### **Sidebar Container**
```css
.sidebar {
    background: linear-gradient(180deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    min-height: 100vh;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    z-index: 1000;
    transition: all 0.3s ease;
}
```

### **Sidebar Brand**
```css
.sidebar-brand {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-brand .logo {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.8rem;
    color: white;
}
```

### **Navigation Items**
```css
.nav-item {
    margin: 0.25rem 0;
}

.nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 0.75rem;
    margin: 0 0.75rem;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(4px);
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-weight: 600;
}
```

## **Background Gradients**

### **Main Background**
```css
body {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}
```

### **Hero Section**
```css
.hero-section {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
}
```

### **Upload Zone**
```css
.upload-zone {
    background: linear-gradient(135deg, #fafbff, #f0f4ff);
    border: 3px dashed #cbd5e1;
    border-radius: 1.5rem;
}
```

## **Animations & Transitions**

### **Hover Effects**
```css
transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
transform: translateY(-2px);
```

### **Fade In Animation**
```css
.fade-in {
    animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
```

### **Slide Up Animation**
```css
.slide-up {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
```

## **Icons & Visual Elements**

### **Icon Library**
- **Font Awesome 6.4.0** for all icons
- **Primary Icons**: `fas fa-upload`, `fas fa-play`, `fas fa-check-circle`, `fas fa-info-circle`

### **Logo Design**
- **Circular logo** with white background and blue gradient
- **Size**: 60px × 60px
- **Background**: `rgba(255, 255, 255, 0.2)`
- **Border radius**: 50%

## **Responsive Design**

### **Breakpoints**
```css
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .upload-zone {
        padding: 2rem 1rem;
        min-height: 250px;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }
}
```

## **Box Shadows**

### **Card Shadows**
```css
box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
```

### **Hover Shadows**
```css
box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
```

### **Button Shadows**
```css
box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
```

## **Status Indicators**

### **API Status Colors**
```css
.api-status-card.connected {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border-color: #10b981;
}

.api-status-card.disconnected {
    background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
    border-color: #ef4444;
}
```

### **Status Indicators**
```css
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
    animation: pulse 2s infinite;
}

.status-indicator.success {
    background-color: #10b981;
}

.status-indicator.error {
    background-color: #ef4444;
}

.status-indicator.warning {
    background-color: #f59e0b;
}
```

## **Loading States**

### **Loading Animation**
```css
.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
```

## **Progress Bars**

### **Progress Bar Styling**
```css
.progress {
    height: 0.75rem;
    border-radius: 1rem;
    background: var(--border-light);
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
    border-radius: 1rem;
    transition: width 0.6s ease;
}
```

This design specification captures the exact colors, typography, spacing, and visual elements used in your BuyPower application. The design follows a modern, professional aesthetic with a blue and green color scheme, rounded corners, subtle shadows, and smooth animations.
