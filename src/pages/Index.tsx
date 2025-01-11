import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useState } from "react";
import { generateSecret, generateToken, verifyToken } from "@/lib/otp";
import { toast } from "sonner";

const Index = () => {
  const [secret] = useState(() => generateSecret());
  const [token, setToken] = useState("");

  const handleVerify = () => {
    const isValid = verifyToken(token, secret);
    if (isValid) {
      toast.success("Code OTP valide !");
    } else {
      toast.error("Code OTP invalide !");
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 className="text-2xl font-bold mb-6 text-center">Authentification 2FA</h1>
        
        <div className="space-y-4">
          <div>
            <p className="text-sm text-gray-600 mb-2">Votre clé secrète :</p>
            <code className="block p-3 bg-gray-100 rounded text-sm break-all">
              {secret}
            </code>
          </div>

          <div>
            <p className="text-sm text-gray-600 mb-2">Token actuel :</p>
            <code className="block p-3 bg-gray-100 rounded text-sm">
              {generateToken(secret)}
            </code>
          </div>

          <div className="space-y-2">
            <Input
              type="text"
              placeholder="Entrez le code OTP"
              value={token}
              onChange={(e) => setToken(e.target.value)}
            />
            <Button 
              className="w-full" 
              onClick={handleVerify}
            >
              Vérifier le code
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Index;