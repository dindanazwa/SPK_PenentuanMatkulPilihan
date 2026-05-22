import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.preprocessing import StandardScaler
import joblib

# 1. Load dataset
data = pd.read_csv("dataset_spk.csv")

# 2. Memisahkan fitur dan label
# X = input model
# y = output prediksi: 1 direkomendasikan, 0 tidak direkomendasikan
X = data[["minat", "kesulitan", "relevansi", "nilai_prasyarat"]]
y = data["label"]

# 3. Scaling fitur
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# 4. Split data training dan testing
X_train, X_test, y_train, y_test = train_test_split(
    X_scaled,
    y,
    test_size=0.2,
    random_state=42
)

# 5. Training model Random Forest
model = RandomForestClassifier(
    n_estimators=100,
    random_state=42
)

model.fit(X_train, y_train)

# 6. Evaluasi model
y_pred = model.predict(X_test)

akurasi = accuracy_score(y_test, y_pred)

print(f"Akurasi Model: {akurasi * 100:.1f}%")
print("\nClassification Report:")
print(classification_report(y_test, y_pred))

# 7. Feature importance
importances = model.feature_importances_

print("\nFeature Importance:")
print(f"Minat: {importances[0]:.3f}")
print(f"Kesulitan: {importances[1]:.3f}")
print(f"Relevansi: {importances[2]:.3f}")
print(f"Nilai Prasyarat: {importances[3]:.3f}")

# 8. Menyimpan model dan scaler
joblib.dump(model, "model_spk.pkl")
joblib.dump(scaler, "scaler_spk.pkl")

print("\nModel dan scaler berhasil disimpan.")