# 西南交通大学日本校友会 Official Website

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**揚華会 | SWJTU Japan Alumni Association**

西南交通大学日本校友会（日語愛称：揚華会）の公式ウェブサイトです。

## 🌐 Website

**Live Site:** [https://swjtu.jp](https://swjtu.jp)

## 📖 About

西南交通大学日本校友会は2015年11月21日に東京で正式に設立されました。日本在住の西南交通大学卒業生の交流促進、母校精神の継承、校友間の協力を目的としています。

Southwest Jiaotong University Japan Alumni Association was officially established in Tokyo on November 21, 2015. Our mission is to connect SWJTU alumni in Japan, preserve our university's traditions, and promote collaboration among alumni.

## 🏗️ Project Structure

```
swjtu.jp/
├── index.html          # 首页 / Homepage
├── about.html          # 关于我们 / About Us
├── news.html           # 最新动态 / News
├── notice.html         # 通知 / Notices
├── photos.html         # 活动照片 / Photo Gallery
├── signup.html         # 校友注册 / Alumni Registration
├── contact.html        # 联系我们 / Contact
├── donate.html         # 捐赠支持 / Donations
├── finance.html        # 财务公开 / Financial Reports
├── privacy.html        # 個人情報保護方針 / Privacy Policy
├── css/
│   ├── style.css       # Main stylesheet
│   ├── fixmenu.css     # Fixed navigation styles
│   └── slide.css       # Image slider styles
├── cgi/
│   └── mailform.php    # Contact form handler
├── images/
│   ├── logo_*.png      # Logo variations
│   └── slides/         # Homepage slideshow images
├── photos/             # Event photo albums
└── docs/               # Documentation files
```

## 🛠️ Technologies

- **HTML5** - Semantic markup
- **CSS3** - Responsive styling with custom properties
- **JavaScript** - Vanilla JS for interactivity
- **PHP** - Server-side mail form processing
- **Font Awesome 6.4** - Icon library
- **Google Analytics** - Website analytics

## ✨ Features

- 📱 Responsive design for mobile and desktop
- 🖼️ Image slideshow on homepage
- 📝 Alumni registration form
- 📧 Contact form with PHP backend
- 🔒 Privacy policy compliance
- 🌏 Multilingual content (Chinese/Japanese)

## 🚀 Deployment

This is a static website that can be deployed to any web server with PHP support.

### Requirements
- Web server (Apache/Nginx)
- PHP 7.0+ (for contact form)

### Setup
1. Clone the repository
   ```bash
   git clone https://github.com/unitlive/swjtu.jp.git
   ```
2. Upload files to your web server
3. Configure PHP mail settings in `cgi/mailform.php`
4. Update Google Analytics ID if needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Contact

- **Website:** [https://swjtu.jp](https://swjtu.jp)
- **Contact Form:** [https://swjtu.jp/contact.html](https://swjtu.jp/contact.html)

## 🔗 Related Links

- [西南交通大学](https://www.swjtu.edu.cn) - Southwest Jiaotong University
- [校友总会](https://www.swjtu.edu.cn/jdxy.htm) - SWJTU Alumni Association

---

**竢实扬华，自强不息**

*Copyright © 2025 西南交通大学日本校友会 All Rights Reserved.*
