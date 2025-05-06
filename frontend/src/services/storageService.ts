import { Capacitor } from '@capacitor/core';
import { Preferences } from '@capacitor/preferences';

/**
 * Service pour gérer le stockage des préférences utilisateur
 * S'adapte automatiquement à la plateforme (web ou native)
 */
export class StorageService {
  // Préfixe utilisé pour les clés de stockage
  private static keyPrefix = 'cesizen_';
  
  /**
   * Détermine si nous sommes sur une plateforme native
   */
  private static isNative(): boolean {
    return Capacitor.isNativePlatform();
  }
  
  /**
   * Stocke une valeur
   * @param key Clé de stockage
   * @param value Valeur à stocker
   */
  public static async set(key: string, value: any): Promise<void> {
    const prefixedKey = this.keyPrefix + key;
    const valueString = typeof value === 'string' ? value : JSON.stringify(value);
    
    if (this.isNative()) {
      // Utiliser le plugin Preferences de Capacitor
      await Preferences.set({
        key: prefixedKey,
        value: valueString
      });
    } else {
      // Utiliser localStorage sur le web
      localStorage.setItem(prefixedKey, valueString);
    }
  }
  
  /**
   * Récupère une valeur
   * @param key Clé de stockage
   * @param defaultValue Valeur par défaut si la clé n'existe pas
   */
  public static async get<T>(key: string, defaultValue?: T): Promise<T | null> {
    const prefixedKey = this.keyPrefix + key;
    
    try {
      let valueString: string | null = null;
      
      if (this.isNative()) {
        // Utiliser le plugin Preferences de Capacitor
        const result = await Preferences.get({ key: prefixedKey });
        valueString = result.value;
      } else {
        // Utiliser localStorage sur le web
        valueString = localStorage.getItem(prefixedKey);
      }
      
      if (valueString === null) {
        return defaultValue || null;
      }
      
      // Essayer de parser en JSON, sinon retourner la valeur brute
      try {
        return JSON.parse(valueString) as T;
      } catch (e) {
        return valueString as unknown as T;
      }
    } catch (error) {
      console.error('Erreur lors de la récupération de la valeur:', error);
      return defaultValue || null;
    }
  }
  
  /**
   * Supprime une valeur
   * @param key Clé de stockage
   */
  public static async remove(key: string): Promise<void> {
    const prefixedKey = this.keyPrefix + key;
    
    if (this.isNative()) {
      // Utiliser le plugin Preferences de Capacitor
      await Preferences.remove({ key: prefixedKey });
    } else {
      // Utiliser localStorage sur le web
      localStorage.removeItem(prefixedKey);
    }
  }
  
  /**
   * Supprime toutes les données de l'application
   */
  public static async clear(): Promise<void> {
    if (this.isNative()) {
      // Utiliser le plugin Preferences de Capacitor
      // Ne supprime que les clés préfixées avec notre préfixe d'application
      const keys = await Preferences.keys();
      const ourKeys = keys.keys.filter(key => key.startsWith(this.keyPrefix));
      
      for (const key of ourKeys) {
        await Preferences.remove({ key });
      }
    } else {
      // Supprimer uniquement les clés qui appartiennent à notre application
      const keys = Object.keys(localStorage);
      const ourKeys = keys.filter(key => key.startsWith(this.keyPrefix));
      
      for (const key of ourKeys) {
        localStorage.removeItem(key);
      }
    }
  }
}