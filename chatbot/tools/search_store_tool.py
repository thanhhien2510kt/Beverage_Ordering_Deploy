"""
Tool: Tìm kiếm cửa hàng (Store Search)
Dựa vào Tỉnh/Thành phố và Quận/Huyện do người dùng cung cấp.
"""
import httpx
from langchain_core.tools import tool
from config import PHP_BASE_URL

@tool
def search_store_tool(district: str, city: str) -> str:
    """
    Tìm kiếm cửa hàng MeowTea Fresh dựa trên Quận/Huyện và Tỉnh/Thành phố.
    Dùng khi khách hàng hỏi "Có cửa hàng nào ở khu vực XYZ không?", "Địa chỉ quán ở đâu?"...
    Tool này yêu cầu LLM phải truyền ĐẦY ĐỦ cả 2 tham số:
    - district: Tên Quận/Huyện (VD: "Quận 10", "Tân Bình", "Cầu Giấy").
    - city: Tên Tỉnh/Thành phố (VD: "Hồ Chí Minh", "Hà Nội", "Đà Nẵng").
    Nếu user cung cấp thiếu 1 trong 2, LLM KHÔNG được gọi tool này mà phải hỏi lại user.
    """
    
    # 1. Tìm chính xác (Cả Quận và Tỉnh)
    try:
        r = httpx.get(
            f"{PHP_BASE_URL}/api/stores/search.php",
            params={"ward": district, "province": city},
            timeout=5.0
        )
        data = r.json()
        exact_stores = data.get("stores", [])
        
        lines = []
        if exact_stores:
            lines.append(f"🏠 **MeowTea Fresh có {len(exact_stores)} cửa hàng tại {district}, {city}:**")
            for st in exact_stores:
                lines.append(f"📍 **{st['TenStore']}**: {st['DiaChi']} (📞 {st['DienThoai']})")
        else:
            # 2. Tìm lân cận (Chỉ Tỉnh/Thành phố)
            r = httpx.get(
                f"{PHP_BASE_URL}/api/stores/search.php",
                params={"province": city},
                timeout=5.0
            )
            data_city = r.json()
            city_stores = data_city.get("stores", [])
            
            lines.append(f"🥺 Dạ MeowTea Fresh chưa có cửa hàng tại **{district}**.")
            if city_stores:
                lines.append(f"Tuy nhiên, tụi mình có {len(city_stores)} cửa hàng khác tại **{city}**, bạn tham khảo nhé:")
                # Chỉ lấy top 5 cửa hàng lân cận cho đỡ dài
                for st in city_stores[:5]:
                    lines.append(f"📍 **{st['TenStore']}**: {st['DiaChi']} (📞 {st['DienThoai']})")
            else:
                lines.append(f"Và hiện tại quán cũng chưa có chi nhánh nào ở **{city}** luôn ạ 😢.")

        # Thêm hướng dẫn để KH chủ động tìm kiếm thêm
        lines.append("\n---")
        lines.append("💡 **Mẹo:** Bạn có thể tự tra cứu toàn bộ mạng lưới cửa hàng tại Website/App MeowTea Fresh (Mục Cửa Hàng).")
        lines.append("Hoặc gọi hotline **(028) 1234 5678** để nhân viên hướng dẫn phần đường đi chi tiết nhé! 🐾")
        
        return "\n".join(lines)
        
    except Exception as e:
        return f"Lỗi khi tìm kiếm cửa hàng: {str(e)}"
