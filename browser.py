import sys
from PyQt5.QtWidgets import QApplication, QMainWindow
from PyQt5.QtWebEngineWidgets import QWebEngineView

class KioskBrowser(QMainWindow):
    def __init__(self):
        super().__init__()

        # Nastavení okna
        self.setWindowTitle("Kioskový prohlížeč")
        self.setGeometry(0, 0, 1920, 1080)  # Rozlišení obrazovky (přizpůsobte podle potřeby)

        # Webový prohlížeč
        self.browser = QWebEngineView()
        self.browser.setUrl("http://localhost")  # Nastavte výchozí URL

        # Přidání do hlavního okna
        self.setCentralWidget(self.browser)

        # Přepnutí do celoobrazovkového režimu
        self.showFullScreen()

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = KioskBrowser()
    window.show()
    sys.exit(app.exec_())
