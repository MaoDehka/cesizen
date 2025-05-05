// src/types/index.ts

// Modèles de données
export interface User {
  id: number;
  name: string;
  email: string;
  role_id: number;
  role?: Role;
  active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface Role {
  id: number;
  name: string;
  description: string;
}

export interface Questionnaire {
  id: number;
  title: string;
  description?: string;
  nb_question: number;
  creation_date: string;
  last_modification: string;
  active: boolean;
  questions?: Question[];
}

export interface Question {
  id: number;
  questionnaire_id: number;
  response_text: string;
  response_score: number;
  date_response: string;
}

export interface Response {
  id: number;
  user_id: number;
  question_id: number;
  reponse: string;
  date: string;
}

export interface Diagnostic {
  id: number;
  user_id: number;
  questionnaire_id?: number;
  score_total: number;
  stress_level: string;
  diagnostic_date: string;
  consequences?: string;
  advices?: string;
  saved: boolean;
  questionnaire?: Questionnaire;
}

export interface Content {
  id: number;
  page: string;
  title: string;
  content: string;
  active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface StressLevel {
  id: number;
  name: string;
  min_score: number;
  max_score: number;
  risk_percentage: number;
  description?: string;
  consequences?: string;
  active: boolean;
  created_at?: string;
  updated_at?: string;
  recommendations?: Recommendation[]; 
}

export interface Recommendation {
  id: number;
  stress_level_id: number;
  description: string;
  details?: string;
  order: number;
  active: boolean;
  created_at?: string;
  updated_at?: string;
}

// Types pour les formulaires
export interface LoginForm {
  email: string;
  password: string;
}

export interface RegisterForm extends LoginForm {
  name: string;
  password_confirmation: string;
}

export interface DiagnosticForm {
  questionnaire_id: number;
  questions: number[];
}

// Types pour l'administration
export interface StressLevelForm {
  name: string;
  min_score: number;
  max_score: number;
  risk_percentage: number;
  description?: string;
  consequences?: string;
  active: boolean;
}

export interface RecommendationForm {
  stress_level_id: number;
  description: string;
  details?: string;
  order: number;
  active: boolean;
}