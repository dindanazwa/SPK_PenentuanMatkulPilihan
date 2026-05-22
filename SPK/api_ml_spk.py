from fastapi import FastAPI
from pydantic import BaseModel
import joblib
import numpy as np

app = FastAPI(title="SPK Mata Kuliah ML API")

# Load model dan scaler
model = joblib.load("model_spk.pkl")
scaler = joblib.load("scaler_spk.pkl")

class MatkulInput(BaseModel):
    nama_matkul: str
    minat: float
    kesulitan: float
    relevansi: float
    nilai_prasyarat: float

class PrediksiOutput(BaseModel):
    nama_matkul: str
    label: int
    probabilitas: float
    keterangan: str

@app.get("/")
def root():
    return {"message": "SPK Mata Kuliah ML API aktif"}

@app.post("/prediksi", response_model=PrediksiOutput)
def prediksi(data: MatkulInput):
    X = np.array([[
        data.minat,
        data.kesulitan,
        data.relevansi,
        data.nilai_prasyarat
    ]])

    X_scaled = scaler.transform(X)

    label = int(model.predict(X_scaled)[0])
    proba = float(model.predict_proba(X_scaled)[0][label])

    keterangan = "Direkomendasikan" if label == 1 else "Tidak Direkomendasikan"

    return {
        "nama_matkul": data.nama_matkul,
        "label": label,
        "probabilitas": round(proba, 4),
        "keterangan": keterangan
    }

@app.post("/prediksi-batch")
def prediksi_batch(items: list[MatkulInput]):
    hasil = []

    for item in items:
        X = np.array([[
            item.minat,
            item.kesulitan,
            item.relevansi,
            item.nilai_prasyarat
        ]])

        X_scaled = scaler.transform(X)

        label = int(model.predict(X_scaled)[0])
        proba = float(model.predict_proba(X_scaled)[0][label])

        hasil.append({
            "nama_matkul": item.nama_matkul,
            "label": label,
            "proba": round(proba, 4),
            "keterangan": "Direkomendasikan" if label == 1 else "Tidak Direkomendasikan"
        })

    return {"hasil": hasil}