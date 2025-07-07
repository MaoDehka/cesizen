import { Capacitor } from '@capacitor/core';
import { App } from '@capacitor/app';
import { StatusBar } from '@capacitor/status-bar';
import { SplashScreen } from '@capacitor/splash-screen';
import { Haptics, ImpactStyle } from '@capacitor/haptics';
import { Toast } from '@capacitor/toast';
import { LocalNotifications } from '@capacitor/local-notifications';

/**
 * Service pour gérer les fonctionnalités spécifiques mobiles
 */
export class MobileService {
  /**
   * Vérifie si l'application s'exécute sur un appareil mobile
   */
  static isNativePlatform(): boolean {
    return Capacitor.isNativePlatform();
  }

  /**
   * Vérifie la plateforme actuelle
   */
  static getPlatform(): string {
    return Capacitor.getPlatform();
  }

  /**
   * Initialise les composants natifs de l'application
   */
  static async initializeApp() {
    if (!this.isNativePlatform()) return;

    // Cacher l'écran de démarrage
    await SplashScreen.hide();

    // Configuration de la barre de statut
    try {
      await StatusBar.setBackgroundColor({ color: '#4CAF50' });
    } catch (error) {
      console.error('Erreur lors de la configuration de la barre de statut:', error);
    }

    // Configurer les événements de l'application
    App.addListener('backButton', ({ canGoBack }) => {
      if (!canGoBack) {
        // Confirmer la sortie de l'application
        App.exitApp();
      } else {
        // Naviguer en arrière
        window.history.back();
      }
    });
  }

  /**
   * Déclenche un retour haptique (vibration)
   */
  static async triggerHapticFeedback() {
    if (!this.isNativePlatform()) return;
    
    try {
      await Haptics.impact({ style: ImpactStyle.Medium });
    } catch (error) {
      console.error('Erreur lors du retour haptique:', error);
    }
  }

  /**
   * Affiche un toast natif
   */
  static async showToast(message: string, duration: 'short' | 'long' = 'short') {
    if (!this.isNativePlatform()) {
      // Fallback pour le web
      alert(message);
      return;
    }

    await Toast.show({
      text: message,
      duration: duration === 'short' ? 'short' : 'long',
      position: 'bottom'
    });
  }

  /**
   * Planifie une notification locale pour les rappels de bien-être
   */
  static async scheduleWellnessReminder(title: string, body: string, hours: number) {
    if (!this.isNativePlatform()) return;

    try {
      // Demander la permission pour les notifications
      const permission = await LocalNotifications.requestPermissions();
      
      if (permission.display !== 'granted') {
        console.warn('Permission de notification non accordée');
        return;
      }

      // Calculer l'heure de la notification
      const triggerTime = new Date();
      triggerTime.setHours(triggerTime.getHours() + hours);

      await LocalNotifications.schedule({
        notifications: [
          {
            title: title,
            body: body,
            id: Math.floor(Math.random() * 100000),
            schedule: { at: triggerTime },
            sound: undefined,
            actionTypeId: '',
            extra: null
          }
        ]
      });
    } catch (error) {
      console.error('Erreur lors de la planification de la notification:', error);
    }
  }
}